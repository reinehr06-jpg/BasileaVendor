<?php

namespace App\Services\Integration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class IntegrationTestService
{
    /**
     * Testar conexão Asaas
     */
    public function testAsaas(): array
    {
        $apiKey = Setting::get('asaas_api_key');
        $environment = Setting::get('asaas_environment', 'sandbox');
        $baseUrl = $environment === 'production' 
            ? 'https://www.asaas.com/api/v3' 
            : 'https://sandbox.asaas.com/api/v3';

        if (!$apiKey) {
            return ['success' => false, 'message' => 'API Key não configurada'];
        }

        try {
            $response = Http::withHeaders(['access_token' => $apiKey])
                ->timeout(10)
                ->get("{$baseUrl}/customers", [
                    'offset' => 0,
                    'limit' => 1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Conexão com Asaas bem-sucedida',
                    'data' => [
                        'total_customers' => $data['totalCount'] ?? 0,
                        'environment' => $environment
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro na API: ' . ($response->json()['message'] ?? $response->status())
            ];
        } catch (\Exception $e) {
            Log::error('Asaas test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()];
        }
    }

    /**
     * Testar conexão com Checkout API
     */
    public function testCheckout(): array
    {
        $apiKey = Setting::get('checkout_api_key');
        $apiUrl = rtrim(Setting::get('checkout_api_url', Setting::get('checkout_external_url', 'http://localhost:8001')), '/');

        if (!$apiKey) {
            return ['success' => false, 'message' => 'Checkout API Key não configurada'];
        }

        if (!$apiUrl || !filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'URL da API inválida ou não configurada'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(10)->get("{$apiUrl}/api/diag-check");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Checkout API conectada com sucesso',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro na API: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Checkout test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()];
        }
    }

    /**
     * Testar webhook Basileia Church
     */
    public function testBasileiaChurchWebhook(): array
    {
        $webhookUrl = Setting::get('basileia_church_webhook_url');
        $webhookToken = Setting::get('basileia_church_webhook_token');

        if (!$webhookUrl || !$webhookToken) {
            return ['success' => false, 'message' => 'Webhook URL ou Token não configurados'];
        }

        try {
            // Enviar payload de teste
            $payload = [
                'event' => 'test.webhook',
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'test' => true,
                    'message' => 'Teste de integração Basileia Church'
                ]
            ];

            $response = Http::withHeaders([
                'X-Webhook-Token' => $webhookToken,
                'Content-Type' => 'application/json'
            ])->timeout(10)->post($webhookUrl, $payload);

            return [
                'success' => $response->successful(),
                'message' => $response->successful() 
                    ? 'Webhook testado com sucesso' 
                    : 'Erro no webhook: ' . $response->status(),
                'response_code' => $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Basileia Church webhook test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Testar conexão Google Calendar
     */
    public function testGoogleCalendar(): array
    {
        $clientId = Setting::get('google_calendar_client_id');
        $clientSecret = Setting::get('google_calendar_client_secret');

        if (!$clientId || !$clientSecret) {
            return ['success' => false, 'message' => 'Credenciais Google Calendar não configuradas'];
        }

        try {
            // Testar se o token de acesso é válido fazendo uma requisição simples
            // Nota: Isso assume que já existe um token armazenado
            $accessToken = Setting::get('google_calendar_access_token');

            if (!$accessToken) {
                return ['success' => false, 'message' => 'Token de acesso não encontrado. Faça autenticação OAuth primeiro.'];
            }

            $response = Http::withToken($accessToken)
                ->timeout(10)
                ->get('https://www.googleapis.com/calendar/v3/calendars/primary/events', [
                    'maxResults' => 1
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Google Calendar conectado com sucesso'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro na API Google: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Google Calendar test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Testar conexão OpenAI (se configurada)
     */
    public function testOpenAI(): array
    {
        $apiKey = Setting::get('openai_api_key');
        $model = Setting::get('openai_model', 'gpt-3.5-turbo');

        if (!$apiKey) {
            return ['success' => false, 'message' => 'OpenAI API Key não configurada'];
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Teste de conexão. Responda apenas: OK']
                    ],
                    'max_tokens' => 5
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'OpenAI conectado com sucesso',
                    'data' => [
                        'model' => $model,
                        'response' => $response->json()['choices'][0]['message']['content'] ?? 'OK'
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro OpenAI: ' . ($response->json()['error']['message'] ?? $response->status())
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Testar conexão Ollama (IA Local)
     */
    public function testOllama(): array
    {
        $endpoint = Setting::get('ia_local_endpoint', 'http://localhost:11434/api/generate');
        $model = Setting::get('ia_local_model', 'llama3.2');

        try {
            $response = Http::timeout(30)->post($endpoint, [
                'model' => $model,
                'prompt' => 'Teste de conexão. Responda apenas: OK',
                'stream' => false
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Ollama conectado com sucesso (modelo: {$model})",
                    'data' => [
                        'model' => $model,
                        'response' => substr(strip_tags($response->json()['response'] ?? ''), 0, 50)
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro no Ollama: ' . $response->status()
            ];
        } catch (\Exception $e) {
            Log::error('Ollama test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Testar SMTP/Email
     */
    public function testEmail(): array
    {
        $host = Setting::get('mail_host');
        $port = Setting::get('mail_port', 2525);
        $username = Setting::get('mail_username');
        $password = Setting::get('mail_password');

        if (!$host || !$username || !$password) {
            return ['success' => false, 'message' => 'Configurações de email incompletas'];
        }

        try {
            $testEmail = Setting::get('email_suporte', 'test@basileia.test');
            
            // Enviar email de teste usando Mail facade
            \Illuminate\Support\Facades\Mail::raw(
                'Teste de integração de email - ' . now(),
                function ($message) use ($testEmail) {
                    $message->to($testEmail)
                            ->subject('Teste de Integração - Basiléia Vendas');
                }
            );

            return ['success' => true, 'message' => 'Email de teste enviado com sucesso para ' . $testEmail];
        } catch (\Exception $e) {
            Log::error('Email test failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Erro ao enviar email: ' . $e->getMessage()];
        }
    }

    /**
     * Executar todos os testes
     */
    public function testAll(): array
    {
        $results = [
            'asaas' => $this->testAsaas(),
            'checkout' => $this->testCheckout(),
            'basileia_church' => $this->testBasileiaChurchWebhook(),
            'google_calendar' => $this->testGoogleCalendar(),
            'openai' => $this->testOpenAI(),
            'ollama' => $this->testOllama(),
            'email' => $this->testEmail(),
        ];

        $successCount = collect($results)->filter(fn($r) => $r['success'])->count();
        $total = count($results);

        return [
            'summary' => [
                'total' => $total,
                'success' => $successCount,
                'failed' => $total - $successCount,
                'all_success' => $successCount === $total
            ],
            'tests' => $results
        ];
    }
}
