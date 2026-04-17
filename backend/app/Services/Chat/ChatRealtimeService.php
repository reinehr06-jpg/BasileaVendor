<?php

namespace App\Services\Chat;

use App\Models\Chat\ChatMessage;
use Illuminate\Support\Facades\Event;
use App\Events\ChatMessageSent;

class ChatRealtimeService
{
    public function broadcastMessage(ChatMessage $message): void
    {
        Event::dispatch(new ChatMessageSent($message));
    }

    public function notifyNewMessage(ChatMessage $message): void
    {
        $this->broadcastMessage($message);
    }

    public function notifyMessageDelivered(int $messageId, string $externalId): void
    {
        $message = ChatMessage::find($messageId);
        if ($message) {
            $message->is_delivered = true;
            $message->delivered_at = now();
            $message->save();
            Event::dispatch(new ChatMessageSent($message, 'delivered'));
        }
    }

    public function notifyMessageRead(int $messageId): void
    {
        $message = ChatMessage::find($messageId);
        if ($message) {
            $message->is_read = true;
            $message->read_at = now();
            $message->save();
            Event::dispatch(new ChatMessageSent($message, 'read'));
        }
    }
}