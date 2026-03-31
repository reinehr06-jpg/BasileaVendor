<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\WebhookOutboundConfig;
use App\Models\WebhookOutboundLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IntegracaoSiteController extends Controller
{
    public function index()
    {
        $apiKeys = ApiKey::orderByDesc('created_at')->get();
        $webhooks = WebhookOutboundConfig::where('service', 'site')->get();
        $recentLogs = WebhookOutboundLog::with('config')
            ->whereHas('config', function ($q) {
                $q->where('service', 'site');
            })
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $checkoutUrl = \App\Models\Setting::get('checkout_external_url', '');

        return view('master.integracoes.site', compact('apiKeys', 'webhooks', 'recentLogs', 'checkoutUrl'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'checkout_external_url' => 'nullable|url|max:500',
        ]);

        \App\Models\Setting::set('checkout_external_url', $request->checkout_external_url);

        return back()->with('success', 'Configurações de integração atualizadas com sucesso');
    }

    public function storeKey(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'service' => 'required|in:site,eventos,other',
            'rate_limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $plainKey = ApiKey::generate();

        ApiKey::create([
            'name' => $request->name,
            'key' => hash('sha256', $plainKey),
            'service' => $request->service,
            'rate_limit' => $request->rate_limit ?? 60,
            'active' => true,
        ]);

        return back()->with('success', "API Key criada! Copie agora: {$plainKey} (não será exibida novamente)");
    }

    public function toggleKey(ApiKey $apiKey)
    {
        $apiKey->update(['active' => !$apiKey->active]);

        return back()->with('success', $apiKey->active ? 'API Key ativada' : 'API Key desativada');
    }

    public function destroyKey(ApiKey $apiKey)
    {
        $apiKey->delete();

        return back()->with('success', 'API Key removida');
    }

    public function storeWebhook(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'events' => 'required|array',
            'events.*' => 'string',
            'secret' => 'nullable|string|max:255',
        ]);

        WebhookOutboundConfig::create([
            'name' => $request->name,
            'service' => 'site',
            'url' => $request->url,
            'events' => $request->events,
            'secret' => $request->secret,
            'active' => true,
            'retry_count' => 3,
        ]);

        return back()->with('success', 'Webhook configurado com sucesso');
    }

    public function destroyWebhook(WebhookOutboundConfig $webhook)
    {
        $webhook->delete();

        return back()->with('success', 'Webhook removido');
    }
}
