<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Services\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LeadWebhookController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function verifyMeta(Request $request)
    {
        $mode = $request->query('hub.mode');
        $token = $request->query('hub.verify_token');
        $challenge = $request->query('hub.challenge');

        $verifyToken = \App\Models\Setting::get('meta_webhook_verify_token', 'meta_vt_x9kP2mQrLnW5');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('[Lead] Meta webhook verificado com sucesso');
            return response($challenge, 200);
        }

        Log::warning('[Lead] Meta webhook verificação falhou', ['mode' => $mode, 'token' => $token]);
        return response('Forbidden', 403);
    }

    public function handleMeta(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $this->leadService->setTenant($tenantId);

        $payload = $request->all();
        Log::info('[Lead] Meta Lead Gen接收 webhook', ['tenant_id' => $tenantId, 'payload' => $payload]);

        if (!$this->validateMetaSignature($request)) {
            Log::warning('[Lead] Meta assinatura inválida');
            return response(['error' => 'Invalid signature'], 401);
        }

        $entry = $payload['entry'][0] ?? [];
        $changes = $entry['changes'] ?? [];

        foreach ($changes as $change) {
            if ($change['field'] !== 'leadgen') continue;

            $value = $change['value'] ?? [];
            $leadgenId = $value['leadgen_id'] ?? null;

            if (!$leadgenId) continue;

            DB::table('lead_inbound_logs')->insert([
                'tenant_id' => $tenantId,
                'source' => 'meta_ads',
                'raw_payload' => json_encode($payload),
                'leadgen_id' => $leadgenId,
                'form_id' => $value['form_id'] ?? null,
                'ad_id' => $value['ad_id'] ?? null,
                'adgroup_id' => $value['adgroup_id'] ?? null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                $leadData = $this->leadService->fetchMetaLeadFromGraph($leadgenId);
                if ($leadData) {
                    $this->leadService->processMetaLead($leadData);
                    DB::table('lead_inbound_logs')
                        ->where('leadgen_id', $leadgenId)
                        ->update(['status' => 'processed', 'updated_at' => now()]);
                }
            } catch (\Exception $e) {
                Log::error('[Lead] Erro ao processar lead Meta', ['error' => $e->getMessage()]);
                DB::table('lead_inbound_logs')
                    ->where('leadgen_id', $leadgenId)
                    ->update(['status' => 'error', 'error_message' => $e->getMessage(), 'updated_at' => now()]);
            }
        }

        return response(['status' => 'received'], 200);
    }

    public function handleLinkedIn(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $this->leadService->setTenant($tenantId);

        $payload = $request->all();
        Log::info('[Lead] LinkedIn lead recebido', ['tenant_id' => $tenantId, 'payload' => $payload]);

        DB::table('lead_inbound_logs')->insert([
            'tenant_id' => $tenantId,
            'source' => 'linkedin',
            'raw_payload' => json_encode($payload),
            'form_id' => $payload['formId'] ?? null,
            'campaign_id' => $payload['campaignId'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->leadService->processLinkedInLead([
                'name' => trim(($payload['firstName'] ?? '') . ' ' . ($payload['lastName'] ?? '')),
                'phone' => $payload['phoneNumbers'][0] ?? null,
                'email' => $payload['emailAddress'] ?? null,
                'form_id' => $payload['formId'] ?? null,
                'campaign_id' => $payload['campaignId'] ?? null,
                'company' => $payload['company'] ?? null,
                'jobTitle' => $payload['jobTitle'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('[Lead] Erro ao processar lead LinkedIn', ['error' => $e->getMessage()]);
        }

        return response(['received' => true], 200);
    }

    public function handleTikTok(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $this->leadService->setTenant($tenantId);

        $payload = $request->all();
        Log::info('[Lead] TikTok lead recebido', ['tenant_id' => $tenantId, 'payload' => $payload]);

        DB::table('lead_inbound_logs')->insert([
            'tenant_id' => $tenantId,
            'source' => 'tiktok',
            'raw_payload' => json_encode($payload),
            'leadgen_id' => $payload['leadId'] ?? null,
            'form_id' => $payload['formId'] ?? null,
            'campaign_id' => $payload['campaignId'] ?? null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->leadService->processTikTokLead([
                'name' => $payload['name'] ?? '',
                'phone' => $payload['phone'] ?? '',
                'email' => $payload['email'] ?? '',
                'advertiser_id' => $payload['advertiserId'] ?? null,
                'lead_id' => $payload['leadId'] ?? null,
                'form_id' => $payload['formId'] ?? null,
                'campaign_id' => $payload['campaignId'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('[Lead] Erro ao processar lead TikTok', ['error' => $e->getMessage()]);
        }

        return response(['received' => true], 200);
    }

    public function handleSite(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $this->leadService->setTenant($tenantId);

        $request->validate([
            'name' => 'required',
            'phone' => 'required',
        ]);

        $ip = $request->ip();
        $rateKey = 'lead_rate:' . $ip;
        
        $rateLimit = (int) \App\Models\Setting::get('lead_rate_limit', 10);
        $rateWindow = (int) \App\Models\Setting::get('lead_rate_window', 3600);

        $currentCount = \Illuminate\Support\Facades\Cache::increment($rateKey);
        if ($currentCount === 1) {
            \Illuminate\Support\Facades\Cache::put($rateKey, 1, $rateWindow);
        }

        if ($currentCount > $rateLimit) {
            return response(['error' => 'Muitas tentativas. Tente mais tarde.'], 429);
        }

        Log::info('[Lead] Site lead recebido', ['tenant_id' => $tenantId, 'ip' => $ip]);

        DB::table('lead_inbound_logs')->insert([
            'tenant_id' => $tenantId,
            'source' => 'site',
            'raw_payload' => json_encode($request->all()),
            'page_url' => $request->input('page_url'),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->leadService->processSiteLead([
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'message' => $request->input('message'),
                'utm_source' => $request->input('utm_source'),
                'utm_medium' => $request->input('utm_medium'),
                'utm_campaign' => $request->input('utm_campaign'),
                'utm_content' => $request->input('utm_content'),
                'page_url' => $request->input('page_url'),
            ]);
        } catch (\Exception $e) {
            Log::error('[Lead] Erro ao processar lead Site', ['error' => $e->getMessage()]);
            return response(['error' => 'Erro ao processar. Tente novamente.'], 500);
        }

        return response(['success' => true, 'message' => 'Em breve entraremos em contato!'], 200);
    }

    protected function getTenantId(Request $request): int
    {
        return $request->header('X-Tenant-ID')
            ? (int) $request->header('X-Tenant-ID')
            : ($request->input('tenant_id') ? (int) $request->input('tenant_id') : 1);
    }

    protected function validateMetaSignature(Request $request): bool
    {
        $signature = $request->header('x-hub-signature-256');
        if (!$signature) {
            $appSecret = \App\Models\Setting::get('meta_app_secret');
            if (!$appSecret) {
                return true;
            }
            return false;
        }

        $appSecret = \App\Models\Setting::get('meta_app_secret');
        if (!$appSecret) {
            return true;
        }

        $payload = $request->getContent();
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);

        return hash_equals($expected, $signature);
    }
}