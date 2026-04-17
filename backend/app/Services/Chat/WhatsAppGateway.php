<?php

namespace App\Services\Chat;

use App\Models\ChatWhatsappConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGateway
{
    protected ?ChatWhatsappConfig $config = null;
    protected string $provider;

    public function __construct(?ChatWhatsappConfig $config = null)
    {
        $this->config = $config;
        $this->provider = $config?->provider ?? 'meta';
    }

    public static function forGestor(int $gestorId): self
    {
        $config = ChatWhatsappConfig::byGestor($gestorId)->ativos()->first();
        return new self($config);
    }

    public function sendMessage(string $to, string $message, ?array $attachment = null): array
    {
        if (!$this->config || !$this->config->is_active) {
            throw new \Exception('WhatsApp não está configurado ou ativo');
        }

        return match($this->provider) {
            'meta' => $this->sendViaMeta($to, $message, $attachment),
            'Take' => $this->sendViaTake($to, $message, $attachment),
            'WppConnect' => $this->sendViaWppConnect($to, $message, $attachment),
            'Evolution' => $this->sendViaEvolution($to, $message, $attachment),
            default => throw new \Exception("Provider {$this->provider} não suportado")
        };
    }

    protected function sendViaMeta(string $to, string $message, ?array $attachment): array
    {
        $url = "https://graph.facebook.com/v18.0/{$this->config->numero_id}/messages";
        
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => $attachment ? 'media' : 'text',
        ];

        if ($attachment) {
            $body['media'] = [
                'link' => $attachment['url']
            ];
        } else {
            $body['text'] = ['body' => $message];
        }

        $response = Http::withToken($this->config->api_token)
            ->post($url, $body);

        if ($response->failed()) {
            Log::error('WhatsApp Meta: Erro ao enviar', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Erro ao enviar mensagem via Meta');
        }

        return $response->json();
    }

    protected function sendViaTake(string $to, string $message, ?array $attachment): array
    {
        $url = "https://api.take.net/v1/whatsapp/messages";
        
        $body = [
            'phone' => $this->normalizePhone($to),
            'message' => $message,
        ];

        $response = Http::withToken($this->config->api_token)
            ->post($url, $body);

        if ($response->failed()) {
            Log::error('WhatsApp Take: Erro ao enviar', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Erro ao enviar mensagem via Take');
        }

        return $response->json();
    }

    protected function sendViaWppConnect(string $to, string $message, ?array $attachment): array
    {
        $url = "{$this->config->api_token}/sendMessage";
        
        $body = [
            'phone' => $this->normalizePhone($to),
            'message' => $message,
        ];

        $response = Http::post($url, $body);

        if ($response->failed()) {
            Log::error('WhatsApp WppConnect: Erro ao enviar', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Erro ao enviar mensagem via WppConnect');
        }

        return $response->json();
    }

    protected function sendViaEvolution(string $to, string $message, ?array $attachment): array
    {
        $url = "{$this->config->api_token}/message/sendText/{$this->normalizePhone($to)}";
        
        $body = [
            'text' => $message
        ];

        $response = Http::post($url, $body);

        if ($response->failed()) {
            Log::error('WhatsApp Evolution: Erro ao enviar', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Erro ao enviar mensagem via Evolution');
        }

        return $response->json();
    }

    public function sendTemplate(string $to, string $templateName, array $params = []): array
    {
        if (!$this->config || !$this->config->is_active) {
            throw new \Exception('WhatsApp não está configurado ou ativo');
        }

        return match($this->provider) {
            'meta' => $this->sendTemplateViaMeta($to, $templateName, $params),
            default => throw new \Exception("Template não suportado para provider {$this->provider}")
        };
    }

    protected function sendTemplateViaMeta(string $to, string $templateName, array $params): array
    {
        $url = "https://graph.facebook.com/v18.0/{$this->config->numero_id}/messages";
        
        $body = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'pt_BR'],
                'components' => []
            ]
        ];

        if (!empty($params)) {
            $body['template']['components'][] = [
                'type' => 'body',
                'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $params)
            ];
        }

        $response = Http::withToken($this->config->api_token)
            ->post($url, $body);

        if ($response->failed()) {
            Log::error('WhatsApp Meta: Erro ao enviar template', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Erro ao enviar template');
        }

        return $response->json();
    }

    public function markAsRead(string $messageId): bool
    {
        if (!$this->config || $this->provider !== 'meta') {
            return false;
        }

        $url = "https://graph.facebook.com/v18.0/{$messageId}";

        $response = Http::withToken($this->config->api_token)
            ->patch($url, [
                'messaging_product' => 'whatsapp',
                'status' => 'read'
            ]);

        return $response->successful();
    }

    public function getWebhookVerifyToken(): ?string
    {
        return $this->config?->webhook_verify_token;
    }

    protected function normalizePhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);
        
        if (substr($clean, 0, 2) === '55' && strlen($clean) >= 12) {
            return '+' . $clean;
        }
        
        if (strlen($clean) === 10 || strlen($clean) === 11) {
            return '+55' . $clean;
        }
        
        return '+55' . $clean;
    }

    public function isConfigured(): bool
    {
        return $this->config && $this->config->isConfigured();
    }
}