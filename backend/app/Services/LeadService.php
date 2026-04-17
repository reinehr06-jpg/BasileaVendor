<?php

namespace App\Services;

use App\Models\LeadInbound;
use App\Models\Chat\ChatContact;
use App\Models\Chat\ChatConversation;
use App\Models\Vendedor;
use App\Models\Setting;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeadService
{
    protected int $tenantId = 1;

    public function setTenant(int $tenantId): self
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    public function process(array $data): LeadInbound
    {
        $source = $data['source'] ?? 'site';
        $phone = isset($data['phone']) ? $this->normalizePhone($data['phone']) : null;
        
        $lead = DB::transaction(function () use ($data, $source, $phone) {
            $existingLead = null;
            if ($phone) {
                $existingLead = LeadInbound::where('tenant_id', $this->tenantId)
                    ->where('phone', $phone)
                    ->where('source', $source)
                    ->where('status', '!=', 'convertido')
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            if ($existingLead) {
                $existingLead->update([
                    'name' => $data['name'] ?? $existingLead->name,
                    'email' => $data['email'] ?? $existingLead->email,
                    'message' => $data['message'] ?? $existingLead->message,
                    'meta' => array_merge($existingLead->meta ?? [], $data['meta'] ?? []),
                    'status' => 'novo',
                ]);
                return $existingLead;
            }

            $vendedorId = $this->assignVendedor($source);

            return LeadInbound::create([
                'tenant_id' => $this->tenantId,
                'vendedor_id' => $vendedorId,
                'name' => $data['name'],
                'phone' => $phone,
                'email' => $data['email'] ?? null,
                'message' => $data['message'] ?? null,
                'source' => $source,
                'status' => 'novo',
                'meta' => $data['meta'] ?? [],
                'utm_source' => $data['utm_source'] ?? null,
                'utm_medium' => $data['utm_medium'] ?? null,
                'utm_campaign' => $data['utm_campaign'] ?? null,
                'utm_content' => $data['utm_content'] ?? null,
                'page_url' => $data['page_url'] ?? null,
            ]);
        });

        $this->createChatContactIfNeeded($lead);

        SystemLog::create([
            'tenant_id' => $this->tenantId,
            'type' => 'lead_created',
            'message' => "Lead criado: {$lead->name} ({$lead->source})",
            'data' => ['lead_id' => $lead->id, 'source' => $source],
        ]);

        return $lead;
    }

    protected function assignVendedor(string $source): ?int
    {
        $roundRobinEnabled = Setting::get('lead_round_robin_enabled', true);
        if (!$roundRobinEnabled) {
            return null;
        }

        $equipeId = Setting::get('lead_default_equipe_id');
        if (!$equipeId) {
            return null;
        }

        $lastAssigned = Setting::where('key', 'lead_last_assigned_vendedor')->first();
        $lastIndex = $lastAssigned ? (int) $lastAssigned->value : -1;

        $vendedores = Vendedor::where('equipe_id', $equipeId)
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->where('lead_enabled', true)
            ->orderBy('id')
            ->get();

        if ($vendedores->isEmpty()) {
            return null;
        }

        $nextIndex = ($lastIndex + 1) % $vendedores->count();
        $nextVendedor = $vendedores[$nextIndex];

        Setting::updateOrCreate(
            ['key' => 'lead_last_assigned_vendedor'],
            ['value' => (string) $nextIndex]
        );

        return $nextVendedor->id;
    }

    protected function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) === 10) {
            $clean = '55' . $clean;
        }
        if (strlen($clean) === 11 && $clean[0] === '0') {
            $clean = '55' . substr($clean, 1);
        }
        return $clean;
    }

    protected function createChatContactIfNeeded(LeadInbound $lead): void
    {
        if (!$lead->phone) {
            return;
        }

        $contact = ChatContact::findOrCreateByPhone($this->tenantId, $lead->phone, [
            'name' => $lead->name,
            'email' => $lead->email,
            'source' => $lead->source,
        ]);

        $lead->chat_contact_id = $contact->id;
        $lead->save();

        ChatConversation::firstOrCreate(
            [
                'tenant_id' => $this->tenantId,
                'contact_id' => $contact->id,
            ],
            [
                'status' => 'open',
                'atendimento_status' => 'nao_atendido',
                'is_resolved' => false,
                'vendedor_id' => $lead->vendedor_id,
            ]
        );
    }

    public function processMetaLead(array $leadData): ?LeadInbound
    {
        $getField = fn($arr, $name) => $arr['field_data'] 
            ? array_find($arr['field_data'], fn($f) => $f['name'] === $name)['values'][0] ?? null
            : null;

        return $this->process([
            'name' => $leadData['name'] ?? '',
            'phone' => $leadData['phone'] ?? '',
            'email' => $leadData['email'] ?? '',
            'source' => 'meta_ads',
            'meta' => [
                'leadgen_id' => $leadData['leadgen_id'] ?? null,
                'form_id' => $leadData['form_id'] ?? null,
                'ad_id' => $leadData['ad_id'] ?? null,
                'adgroup_id' => $leadData['adgroup_id'] ?? null,
                'campaign_id' => $leadData['campaign_id'] ?? null,
                'created_time' => $leadData['created_time'] ?? null,
            ],
        ]);
    }

    public function fetchMetaLeadFromGraph(string $leadgenId): ?array
    {
        $accessToken = Setting::get('meta_page_access_token');
        if (!$accessToken) {
            Log::warning('LeadService: meta_page_access_token não configurado');
            return null;
        }

        try {
            $response = Http::timeout(30)->get("https://graph.facebook.com/v19.0/{$leadgenId}", [
                'access_token' => $accessToken,
                'fields' => 'field_data,created_time,ad_id,adgroup_id,campaign_id,form_id',
            ]);

            if (!$response->successful()) {
                Log::error('LeadService: erro ao buscar lead Meta', ['response' => $response->body()]);
                return null;
            }

            $data = $response->json();
            $fieldData = $data['field_data'] ?? [];

            $get = fn($name) => collect($fieldData)->firstWhere('name', $name)['values'][0] ?? null;

            return [
                'leadgen_id' => $leadgenId,
                'name' => $get('full_name') ?? trim(($get('first_name') ?? '') . ' ' . ($get('last_name') ?? '')),
                'phone' => $this->normalizePhone($get('phone_number') ?? ''),
                'email' => $get('email'),
                'form_id' => $data['form_id'] ?? null,
                'ad_id' => $data['ad_id'] ?? null,
                'adgroup_id' => $data['adgroup_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'created_time' => $data['created_time'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('LeadService: exception ao buscar lead Meta', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function processLinkedInLead(array $data): LeadInbound
    {
        return $this->process([
            'name' => $data['name'] ?? ($data['firstName'] . ' ' . $data['lastName']),
            'phone' => $data['phone'],
            'email' => $data['email'],
            'source' => 'linkedin',
            'meta' => [
                'form_id' => $data['form_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'company' => $data['company'] ?? null,
                'job_title' => $data['jobTitle'] ?? null,
            ],
        ]);
    }

    public function processTikTokLead(array $data): LeadInbound
    {
        return $this->process([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'source' => 'tiktok',
            'meta' => [
                'advertiser_id' => $data['advertiser_id'],
                'lead_id' => $data['lead_id'],
                'form_id' => $data['form_id'],
                'campaign_id' => $data['campaign_id'],
            ],
        ]);
    }

    public function processSiteLead(array $data): LeadInbound
    {
        return $this->process([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'message' => $data['message'],
            'source' => 'site',
            'utm_source' => $data['utm_source'],
            'utm_medium' => $data['utm_medium'],
            'utm_campaign' => $data['utm_campaign'],
            'utm_content' => $data['utm_content'],
            'page_url' => $data['page_url'],
        ]);
    }
}

function array_find(array $arr, callable $fn)
{
    foreach ($arr as $item) {
        if ($fn($item)) {
            return $item;
        }
    }
    return null;
}