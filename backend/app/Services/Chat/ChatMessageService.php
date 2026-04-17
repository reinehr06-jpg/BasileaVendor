<?php

namespace App\Services\Chat;

use App\Models\Chat\ChatContact;
use App\Models\Chat\ChatConversation;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatProviderConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatMessageService
{
    public function handleIncomingMessage(int $tenantId, array $payload, string $provider): ?ChatMessage
    {
        $phone = $payload['phone'] ?? $payload['from'] ?? null;
        $content = $payload['message'] ?? $payload['text'] ?? '';
        $externalId = $payload['message_id'] ?? $payload['id'] ?? null;
        $sourceId = $payload['message_id'] ?? null;
        $name = $payload['name'] ?? null;
        $mediaUrl = $payload['media_url'] ?? $payload['mediaUrl'] ?? null;
        $mediaType = $payload['media_type'] ?? $payload['mediaType'] ?? null;

        if (!$phone || !$content) {
            Log::warning('ChatMessageService: payload sem phone ou content', $payload);
            return null;
        }

        if (ChatMessage::existsByExternalId($externalId)) {
            Log::info('ChatMessageService: mensagem duplicada ignorada', ['external_id' => $externalId]);
            return null;
        }

        $contact = ChatContact::findOrCreateByPhone($tenantId, $phone, [
            'name' => $name,
            'source' => $provider,
            'external_id' => $payload['contact_id'] ?? null,
        ]);

        $conversation = ChatConversation::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'contact_id' => $contact->id,
            ],
            [
                'status' => 'open',
                'atendimento_status' => 'nao_atendido',
                'is_resolved' => false,
            ]
        );

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $contact->id,
            'direction' => 'inbound',
            'content' => $content,
            'type' => $mediaUrl ? ($mediaType ?: 'media') : 'text',
            'external_message_id' => $externalId,
            'source_id' => $sourceId,
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
        ]);

        $conversation->updateTimestampsForMessage('inbound');

        return $message;
    }

    public function sendMessage(int $tenantId, int $conversationId, string $content, ?string $mediaUrl = null): ?ChatMessage
    {
        $conversation = ChatConversation::with('contact')->find($conversationId);
        if (!$conversation || $conversation->status !== 'open') {
            return null;
        }

        $providerConfig = ChatProviderConfig::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if (!$providerConfig) {
            $providerConfig = ChatProviderConfig::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->first();
        }

        if (!$providerConfig) {
            Log::warning('ChatMessageService: nenhum provider configurado', ['tenant_id' => $tenantId]);
            return null;
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'contact_id' => $conversation->contact_id,
            'vendedor_id' => $conversation->vendedor_id,
            'direction' => 'outbound',
            'content' => $content,
            'type' => $mediaUrl ? 'media' : 'text',
            'media_url' => $mediaUrl,
        ]);

        $this->sendViaProvider($providerConfig, $conversation->contact->phone, $content, $mediaUrl, $message->id);

        $conversation->updateTimestampsForMessage('outbound');

        return $message;
    }

    protected function sendViaProvider(ChatProviderConfig $config, string $phone, string $content, ?string $mediaUrl, int $messageId): void
    {
        $result = match ($config->provider) {
            'whatsapp' => $this->sendWhatsApp($config, $phone, $content, $mediaUrl),
            'meta' => $this->sendMeta($config, $phone, $content, $mediaUrl),
            'google' => $this->sendGoogleRcs($config, $phone, $content, $mediaUrl),
            default => ['success' => false, 'error' => 'Provider desconhecido'],
        };

        if ($result['success']) {
            $message = ChatMessage::find($messageId);
            if ($message) {
                $message->external_message_id = $result['message_id'] ?? null;
                $message->is_delivered = true;
                $message->delivered_at = now();
                $message->save();
            }
        } else {
            Log::error('ChatMessageService: falha ao enviar via provider', [
                'provider' => $config->provider,
                'error' => $result['error'] ?? 'erro desconhecido',
                'message_id' => $messageId,
            ]);
        }
    }

    protected function sendWhatsApp(ChatProviderConfig $config, string $phone, string $content, ?string $mediaUrl): array
    {
        try {
            $data = [
                'phone' => $phone,
                'message' => $content,
            ];
            if ($mediaUrl) {
                $data['media_url'] = $mediaUrl;
            }

            $response = Http::timeout(30)->post($config->config['webhook_url'] ?? $config->config['api_url'], $data);
            return ['success' => $response->successful(), 'message_id' => $response->json('message_id')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function sendMeta(ChatProviderConfig $config, string $phone, string $content, ?string $mediaUrl): array
    {
        try {
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
                'type' => $mediaUrl ? 'image' : 'text',
            ];

            if ($mediaUrl) {
                $data['image'] = ['link' => $mediaUrl];
            } else {
                $data['text'] = ['body' => $content];
            }

            $response = Http::timeout(30)
                ->withToken($config->config['access_token'])
                ->post('https://graph.facebook.com/v18.0/' . $config->config['phone_number_id'] . '/messages', $data);

            return ['success' => $response->successful(), 'message_id' => $response->json('messages.0.id')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function sendGoogleRcs(ChatProviderConfig $config, string $phone, string $content, ?string $mediaUrl): array
    {
        try {
            $data = [
                'recipient' => ['phone_number' => $phone],
                'message' => ['text' => $content],
            ];
            if ($mediaUrl) {
                $data['message']['media'] = ['uri' => $mediaUrl];
            }

            $response = Http::timeout(30)
                ->withToken($config->config['api_key'])
                ->post($config->config['api_url'] . '/messages', $data);

            return ['success' => $response->successful(), 'message_id' => $response->json('messageId')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function markAsRead(int $messageId, int $vendedorId): void
    {
        $message = ChatMessage::find($messageId);
        if ($message && $message->direction === 'inbound') {
            $message->is_read = true;
            $message->read_at = now();
            $message->save();

            $conversation = $message->conversation;
            if ($conversation) {
                $conversation->unread_count = max(0, $conversation->unread_count - 1);
                $conversation->save();
            }
        }
    }
}