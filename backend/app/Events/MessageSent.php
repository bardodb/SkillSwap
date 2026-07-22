<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing('sender:id,uuid,name,avatar');
    }

    public function broadcastOn(): array
    {
        $min = min($this->message->sender_id, $this->message->receiver_id);
        $max = max($this->message->sender_id, $this->message->receiver_id);

        return [new PrivateChannel("conversation.{$min}.{$max}")];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'exchange_id' => $this->message->exchange_id,
            'created_at' => $this->message->created_at,
            'sender' => [
                // Public UUID — matches HasPublicUuid / frontend currentUserId comparisons.
                'id' => $this->message->sender->uuid,
                'name' => $this->message->sender->name,
                'avatar' => $this->message->sender->avatar,
            ],
        ];
    }
}
