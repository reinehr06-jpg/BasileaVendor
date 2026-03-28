<?php

namespace App\Services\Checkout;

use App\Models\Lead;
use Illuminate\Support\Facades\Log;

class LeadService
{
    public function createOrUpdate(array $data): Lead
    {
        $document = !empty($data['document']) ? preg_replace('/\D/', '', $data['document']) : null;

        // Buscar lead existente por email ou documento
        $lead = null;

        if (!empty($data['email'])) {
            $lead = Lead::where('email', $data['email'])->first();
        }

        if (!$lead && $document) {
            $lead = Lead::where('document', $document)->first();
        }

        if ($lead) {
            // Atualizar lead existente
            $lead->update([
                'name' => $data['name'] ?? $lead->name,
                'phone' => $data['phone'] ?? $lead->phone,
                'church_name' => $data['church_name'] ?? $lead->church_name,
                'members_count' => $data['members_count'] ?? $lead->members_count,
                'currency' => $data['currency'] ?? $lead->currency,
                'language' => $data['language'] ?? $lead->language,
                'seller_id' => $data['seller_id'] ?? $lead->seller_id,
                'seller_name' => $data['seller_name'] ?? $lead->seller_name,
                'source' => $data['source'] ?? $lead->source,
                'campaign' => $data['campaign'] ?? $lead->campaign,
            ]);

            Log::info('LeadService: Lead atualizado', ['lead_id' => $lead->id]);
        } else {
            // Criar novo lead
            $lead = Lead::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'document' => $document,
                'church_name' => $data['church_name'] ?? null,
                'members_count' => $data['members_count'] ?? null,
                'ip' => request()->ip(),
                'country_code' => $data['country_code'] ?? null,
                'country_name' => $data['country_name'] ?? null,
                'currency' => $data['currency'] ?? 'BRL',
                'language' => $data['language'] ?? 'pt-BR',
                'source' => $data['source'] ?? null,
                'campaign' => $data['campaign'] ?? null,
                'referrer' => $data['referrer'] ?? request()->header('Referer'),
                'seller_id' => $data['seller_id'] ?? null,
                'seller_name' => $data['seller_name'] ?? null,
                'status' => 'new',
            ]);

            Log::info('LeadService: Novo lead criado', ['lead_id' => $lead->id]);
        }

        return $lead;
    }

    public function markAsContacted(Lead $lead): void
    {
        if ($lead->status === 'new') {
            $lead->update(['status' => 'contacted']);
        }
    }

    public function markAsConverted(Lead $lead): void
    {
        $lead->update(['status' => 'converted']);
    }

    public function markAsAbandoned(Lead $lead): void
    {
        if (in_array($lead->status, ['new', 'contacted'])) {
            $lead->update(['status' => 'abandoned']);
        }
    }

    public function getByEmail(string $email): ?Lead
    {
        return Lead::where('email', $email)->first();
    }

    public function getByDocument(string $document): ?Lead
    {
        $document = preg_replace('/\D/', '', $document);
        return Lead::where('document', $document)->first();
    }

    public function getAbandonedLeads(int $hoursAgo = 24): \Illuminate\Database\Eloquent\Collection
    {
        return Lead::where('status', 'abandoned')
            ->where('updated_at', '<', now()->subHours($hoursAgo))
            ->get();
    }
}
