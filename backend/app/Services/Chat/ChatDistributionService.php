<?php

namespace App\Services\Chat;

use App\Models\Chat\ChatContact;
use App\Models\Chat\ChatConversation;
use App\Models\Chat\ChatMessage;
use App\Models\Vendedor;
use Illuminate\Support\Facades\DB;

class ChatDistributionService
{
    public function assignToVendedor(ChatConversation $conversation, int $vendedorId): bool
    {
        return DB::transaction(function () use ($conversation, $vendedorId) {
            $vendedor = Vendedor::find($vendedorId);
            if (!$vendedor || !$vendedor->user || $vendedor->user->status !== 'active') {
                return false;
            }

            $conversation->vendedor_id = $vendedorId;
            $conversation->assigned_at = now();
            $conversation->save();

            return true;
        });
    }

    public function assignRoundRobin(int $tenantId, int $equipeId): ?ChatConversation
    {
        return DB::transaction(function () use ($tenantId, $equipeId) {
            $vendedores = Vendedor::where('equipe_id', $equipeId)
                ->whereHas('user', function ($q) {
                    $q->where('status', 'active');
                })
                ->where('chat_enabled', true)
                ->get();

            if ($vendedores->isEmpty()) {
                return null;
            }

            $lastAssigned = \App\Models\Setting::where('key', 'chat_last_assigned_vendedor_' . $equipeId)->first();
            $lastIndex = $lastAssigned ? (int) $lastAssigned->value : -1;

            $nextIndex = ($lastIndex + 1) % $vendedores->count();
            $nextVendedor = $vendedores[$nextIndex];

            \App\Models\Setting::updateOrCreate(
                ['key' => 'chat_last_assigned_vendedor_' . $equipeId],
                ['value' => (string) $nextIndex]
            );

            $conversation = ChatConversation::where('tenant_id', $tenantId)
                ->whereNull('vendedor_id')
                ->where('status', 'open')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();

            if ($conversation) {
                $conversation->vendedor_id = $nextVendedor->id;
                $conversation->assigned_at = now();
                $conversation->save();
                return $conversation;
            }

            return null;
        });
    }

    public function transferConversation(ChatConversation $conversation, int $newVendedorId): bool
    {
        return DB::transaction(function () use ($conversation, $newVendedorId) {
            $oldVendedorId = $conversation->vendedor_id;
            $conversation->vendedor_id = $newVendedorId;
            $conversation->assigned_at = now();
            $conversation->save();

            \App\Models\SystemLog::create([
                'tenant_id' => $conversation->tenant_id,
                'type' => 'chat_transfer',
                'message' => "Conversa #{$conversation->id} transferida de vendedor #{$oldVendedorId} para #{$newVendedorId}",
                'data' => [
                    'conversation_id' => $conversation->id,
                    'from_vendedor' => $oldVendedorId,
                    'to_vendedor' => $newVendedorId,
                ],
            ]);

            return true;
        });
    }

    public function processInatividade(int $slaMinutes = 60): int
    {
        $processed = 0;
        $conversations = ChatConversation::where('status', 'open')
            ->where('is_resolved', false)
            ->whereNotNull('vendedor_id')
            ->get();

        foreach ($conversations as $conversation) {
            if ($conversation->checkSlaAndTransfer($slaMinutes)) {
                $gestorId = $conversation->vendedor->gestor_id;
                if ($gestorId) {
                    $newVendedor = Vendedor::where('equipe_id', $conversation->vendedor->equipe_id)
                        ->where('gestor_id', $gestorId)
                        ->whereHas('user', fn($q) => $q->where('status', 'active'))
                        ->where('chat_enabled', true)
                        ->inRandomOrder()
                        ->first();

                    if ($newVendedor && $newVendedor->id !== $conversation->vendedor_id) {
                        $this->transferConversation($conversation, $newVendedor->id);
                        $processed++;
                    }
                }
            }
        }

        return $processed;
    }
}