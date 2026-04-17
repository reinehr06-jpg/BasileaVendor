<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\ChatProviderConfig;
use App\Models\Chat\ChatMessage;
use App\Services\Chat\ChatMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatWebhookController extends Controller
{
    protected ChatMessageService $messageService;

    public function __construct(ChatMessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function handleWhatsApp(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $provider = 'whatsapp';

        Log::info('ChatWebhook: received WhatsApp webhook', [
            'tenant_id' => $tenantId,
            'payload' => $request->all(),
        ]);

        $payload = $request->all();
        $messageData = $this->normalizeWhatsAppPayload($payload);

        if (!$messageData) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $message = $this->messageService->handleIncomingMessage($tenantId, $messageData, $provider);

        if (!$message) {
            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'received', 'message_id' => $message->id], 200);
    }

    public function handleMeta(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $provider = 'meta';

        Log::info('ChatWebhook: received Meta webhook', [
            'tenant_id' => $tenantId,
            'payload' => $request->all(),
        ]);

        $entry = $request->input('entry.0', []);
        $changes = $entry['changes'] ?? [];

        foreach ($changes as $change) {
            $messages = $change['value'] ?? [];
            $messagingEvents = $messages['messaging'] ?? [];

            foreach ($messagingEvents as $event) {
                if (isset($event['message'])) {
                    $msg = $event['message'];
                    $sender = $event['sender']['id'] ?? null;

                    if (!$sender) continue;

                    $messageData = [
                        'phone' => $sender,
                        'message' => $msg['text'] ?? $msg['image'] ?? 'Mensagem sem conteúdo',
                        'message_id' => $msg['id'] ?? null,
                        'name' => $event['sender']['profile_name'] ?? null,
                    ];

                    $this->messageService->handleIncomingMessage($tenantId, $messageData, $provider);
                }
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    public function handleGoogle(Request $request)
    {
        $tenantId = $this->getTenantId($request);
        $provider = 'google';

        Log::info('ChatWebhook: received Google RCS webhook', [
            'tenant_id' => $tenantId,
            'payload' => $request->all(),
        ]);

        $payload = $request->all();
        $messageData = $this->normalizeGooglePayload($payload);

        if (!$messageData) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $message = $this->messageService->handleIncomingMessage($tenantId, $messageData, $provider);

        return response()->json(['status' => 'received'], 200);
    }

    public function handleProvider(Request $request, string $provider)
    {
        $tenantId = $this->getTenantId($request);

        $config = ChatProviderConfig::where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return response()->json(['error' => 'Provider não configurado'], 404);
        }

        $normalized = match ($provider) {
            'whatsapp' => $this->normalizeWhatsAppPayload($request->all()),
            'meta' => $this->normalizeMetaPayload($request->all()),
            'google' => $this->normalizeGooglePayload($request->all()),
            default => null,
        };

        if (!$normalized) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $message = $this->messageService->handleIncomingMessage($tenantId, $normalized, $provider);

        return response()->json(['status' => 'received', 'message_id' => $message?->id], 200);
    }

    protected function getTenantId(Request $request): int
    {
        return $request->header('X-Tenant-ID') 
            ? (int) $request->header('X-Tenant-ID') 
            : ($request->input('tenant_id') ? (int) $request->input('tenant_id') : 1);
    }

    protected function normalizeWhatsAppPayload(array $payload): ?array
    {
        if (isset($payload['type']) && $payload['type'] === 'text') {
            return [
                'phone' => $payload['from'] ?? null,
                'message' => $payload['text']['body'] ?? null,
                'message_id' => $payload['id'] ?? null,
                'name' => $payload['sender_name'] ?? null,
            ];
        }

        if (isset($payload['type']) && $payload['type'] === 'image') {
            return [
                'phone' => $payload['from'] ?? null,
                'message' => 'Imagem recebida',
                'message_id' => $payload['id'] ?? null,
                'media_url' => $payload['image']['link'] ?? null,
                'media_type' => 'image',
                'name' => $payload['sender_name'] ?? null,
            ];
        }

        if (isset($payload['messages'])) {
            $msg = $payload['messages'][0] ?? [];
            return [
                'phone' => $msg['from'] ?? null,
                'message' => $msg['text']['body'] ?? $msg['image']['caption'] ?? '',
                'message_id' => $msg['id'] ?? null,
                'name' => $msg['sender_name'] ?? null,
            ];
        }

        return null;
    }

    protected function normalizeMetaPayload(array $payload): ?array
    {
        $entry = $payload['entry'][0] ?? [];
        $changes = $entry['changes'][0] ?? [];
        $value = $changes['value'] ?? [];

        $messaging = $value['messaging'][0] ?? [];
        if (!$messaging) return null;

        $message = $messaging['message'] ?? [];
        return [
            'phone' => $messaging['sender']['id'] ?? null,
            'message' => $message['text'] ?? 'Mensagem recebida',
            'message_id' => $message['mid'] ?? null,
            'name' => $messaging['sender']['profile_name'] ?? null,
        ];
    }

    protected function normalizeGooglePayload(array $payload): ?array
    {
        $message = $payload['message'] ?? [];
        return [
            'phone' => $payload['sender']['phone_number'] ?? null,
            'message' => $message['text'] ?? $message['media'] ?? '',
            'message_id' => $message['messageId'] ?? null,
            'name' => $payload['sender']['displayName'] ?? null,
        ];
    }
}