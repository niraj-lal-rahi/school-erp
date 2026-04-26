<?php

namespace App\Listeners\Communication;

use App\Events\Communication\MessageSent;
use Illuminate\Support\Facades\Log;

class LogMessageSent
{
    public function handle(MessageSent $event): void
    {
        Log::info('communication.message.sent', [
            'message_id' => $event->message->id,
            'conversation_id' => $event->message->conversation_id,
            'sender_type' => $event->message->sender_type,
            'sender_id' => $event->message->sender_id,
            'recipient_type' => $event->message->recipient_type,
            'recipient_id' => $event->message->recipient_id,
            'message_type' => $event->message->message_type,
        ]);
    }
}
