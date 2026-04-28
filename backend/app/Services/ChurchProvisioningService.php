<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Venda;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChurchProvisioningService
{
    protected string $baseUrl;
    protected string $secret;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.church.url', ''), '/');
        $this->secret  = config('services.church.secret', '');
    }

    /**
     * Cria conta no Basiléia Church para o cliente após pagamento confirmado.
     */
    public function criarConta(Cliente $cliente, Venda $venda): void
    {
        if (empty($this->baseUrl) || empty($this->secret)) {
            Log::warning('[Church] Configuração não definida. Pulando provisionamento.', [
                'cliente_id' => $cliente->id,
                'venda_id'   => $venda->id,
            ]);
            return;
        }

        // Se já existe conta no Church, não recria
        if ($cliente->church_user_id) {
            Log::info('[Church] Cliente já possui conta no Church. Pulando.', [
                'cliente_id'     => $cliente->id,
                'church_user_id' => $cliente->church_user_id,
            ]);
            return;
        }

        // Gera senha provisória
        $senhaProvisoria = $this->gerarSenha();

        $payload = [
            'name'             => $cliente->nome_igreja ?? $cliente->nome ?? 'Cliente',
            'email'            => $cliente->email,
            'document'         => $cliente->documento,
            'phone'            => $cliente->whatsapp ?? $cliente->contato ?? '',
            'password'         => $senhaProvisoria,
            'plan'             => $venda->plano ?? 'basic',
            'active'           => true,
            'vendas_venda_id'  => $venda->id,
            'nome_pastor'      => $cliente->nome_pastor ?? '',
            'localidade'       => $cliente->localidade ?? '',
            'quantidade_membros' => $cliente->quantidade_membros ?? 0,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secret,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post("{$this->baseUrl}/api/provisioning/create-account", $payload);

            if ($response->successful()) {
                $data = $response->json();

                $cliente->church_user_id = $data['user_id'] ?? $data['id'] ?? null;
                $cliente->church_account_created_at = now();
                $cliente->save();

                Log::info('[Church] Conta criada com sucesso', [
                    'cliente_id'      => $cliente->id,
                    'church_user_id'  => $cliente->church_user_id,
                    'venda_id'        => $venda->id,
                ]);

                // Notificar outros sistemas (Financeiro, etc.)
                $this->notificarSistemasExternos($cliente, $venda, 'ACCOUNT_CREATED');
            } else {
                Log::error('[Church] Falha ao criar conta', [
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                    'cliente_id' => $cliente->id,
                    'venda_id'   => $venda->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[Church] Exceção ao criar conta', [
                'error'      => $e->getMessage(),
                'cliente_id' => $cliente->id,
                'venda_id'   => $venda->id,
            ]);
        }
    }

    /**
     * Gera senha provisória segura.
     */
    private function gerarSenha(): string
    {
        $maiusculas = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $minusculas = 'abcdefghjkmnpqrstuvwxyz';
        $numeros    = '23456789';
        $especiais  = '@#$%&*';

        $senha = '';
        $senha .= $maiusculas[random_int(0, strlen($maiusculas) - 1)];
        $senha .= $minusculas[random_int(0, strlen($minusculas) - 1)];
        $senha .= $numeros[random_int(0, strlen($numeros) - 1)];
        $senha .= $especiais[random_int(0, strlen($especiais) - 1)];

        $todos = $maiusculas . $minusculas . $numeros . $especiais;
        for ($i = 0; $i < 8; $i++) {
            $senha .= $todos[random_int(0, strlen($todos) - 1)];
        }

        return str_shuffle($senha);
    }

    /**
     * Suspender conta no Church (pagamento vencido).
     */
    public function suspenderConta(Cliente $cliente): void
    {
        if (empty($this->baseUrl) || empty($this->secret)) {
            return;
        }

        if (!$cliente->church_user_id) {
            Log::warning('[Church] Tentativa de suspender conta sem church_user_id', [
                'cliente_id' => $cliente->id,
            ]);
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secret,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])->timeout(30)->patch("{$this->baseUrl}/api/provisioning/suspend-account", [
                'church_user_id' => $cliente->church_user_id,
                'reason'         => 'payment_overdue',
            ]);

            if ($response->successful()) {
                Log::info('[Church] Conta suspensa por inadimplência', [
                    'cliente_id'     => $cliente->id,
                    'church_user_id' => $cliente->church_user_id,
                ]);

                // Notificar outros sistemas
                $this->notificarSistemasExternos($cliente, null, 'ACCOUNT_SUSPENDED');
            } else {
                Log::error('[Church] Falha ao suspender conta', [
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                    'cliente_id' => $cliente->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[Church] Exceção ao suspender conta', [
                'error'      => $e->getMessage(),
                'cliente_id' => $cliente->id,
            ]);
        }
    }

    /**
     * Reativar conta no Church (pagamento confirmado após vencimento).
     */
    public function reativarConta(Cliente $cliente): void
    {
        if (empty($this->baseUrl) || empty($this->secret)) {
            return;
        }

        if (!$cliente->church_user_id) {
            Log::warning('[Church] Tentativa de reativar conta sem church_user_id', [
                'cliente_id' => $cliente->id,
            ]);
            return;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secret,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ])->timeout(30)->patch("{$this->baseUrl}/api/provisioning/reactivate-account", [
                'church_user_id' => $cliente->church_user_id,
            ]);

            if ($response->successful()) {
                Log::info('[Church] Conta reativada após pagamento', [
                    'cliente_id'     => $cliente->id,
                    'church_user_id' => $cliente->church_user_id,
                ]);

                // Notificar outros sistemas
                $this->notificarSistemasExternos($cliente, null, 'ACCOUNT_REACTIVATED');
            } else {
                Log::error('[Church] Falha ao reativar conta', [
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                    'cliente_id' => $cliente->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[Church] Exceção ao reativar conta', [
                'error'      => $e->getMessage(),
                'cliente_id' => $cliente->id,
            ]);
        }
    }

    /**
     * Notificar sistemas externos (ex: Financeiro) sobre mudanças no status.
     */
    public function notificarSistemasExternos(Cliente $cliente, ?Venda $venda, string $evento): void
    {
        $financeiroUrl = \App\Models\Setting::get('external_webhook_financeiro_url', '');
        $financeiroToken = \App\Models\Setting::get('external_webhook_financeiro_token', '');

        if (empty($financeiroUrl)) {
            return;
        }

        $payload = [
            'event'          => $evento,
            'timestamp'      => now()->toIso8601String(),
            'customer' => [
                'id'         => $cliente->id,
                'name'       => $cliente->nome_igreja ?? $cliente->nome,
                'document'   => $cliente->documento,
                'email'      => $cliente->email,
            ],
            'metadata' => [
                'venda_id'   => $venda ? $venda->id : null,
                'plano'      => $venda ? $venda->plano : null,
                'church_id'  => $cliente->church_user_id,
            ]
        ];

        try {
            $response = Http::timeout(10);
            
            if (!empty($financeiroToken)) {
                $response = $response->withToken($financeiroToken);
            }

            $response->post($financeiroUrl, $payload);

            Log::info("[Webhook] Notificação enviada para sistema externo ($evento)", [
                'url' => $financeiroUrl,
                'cliente_id' => $cliente->id
            ]);
        } catch (\Exception $e) {
            Log::warning("[Webhook] Falha ao notificar sistema externo ($evento)", [
                'error' => $e->getMessage(),
                'url' => $financeiroUrl
            ]);
        }
    }
}
