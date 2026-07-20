<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Exchange;
use App\Models\Message;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class MessageService
{
    public const LIVE_STATUSES = [
        Exchange::STATUS_PENDING,
        Exchange::STATUS_ACCEPTED,
        Exchange::STATUS_SCHEDULED,
    ];

    public function canMessage(User $user, int $partnerId): bool
    {
        return $this->latestLiveExchangeId($user, $partnerId) !== null;
    }

    public function latestLiveExchangeId(User $user, int $partnerId): ?int
    {
        return Exchange::query()
            ->where(function ($q) use ($user, $partnerId) {
                $q->where(function ($inner) use ($user, $partnerId) {
                    $inner->where('initiator_id', $user->id)->where('receiver_id', $partnerId);
                })->orWhere(function ($inner) use ($user, $partnerId) {
                    $inner->where('initiator_id', $partnerId)->where('receiver_id', $user->id);
                });
            })
            ->whereIn('status', self::LIVE_STATUSES)
            ->orderByDesc('id')
            ->value('id');
    }

    public function send(User $sender, int $receiverId, string $content, ?int $exchangeId = null): Message
    {
        if ($sender->id === $receiverId) {
            throw ValidationException::withMessages([
                'receiver_id' => ['Não é possível enviar mensagem para si mesmo'],
            ]);
        }

        if (! $this->canMessage($sender, $receiverId)) {
            throw ValidationException::withMessages([
                'receiver_id' => ['Só é possível enviar mensagens enquanto houver uma troca ativa entre vocês'],
            ]);
        }

        $exchangeId = $exchangeId ?? $this->latestLiveExchangeId($sender, $receiverId);

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'exchange_id' => $exchangeId,
            'content' => $content,
            'is_read' => false,
        ]);

        broadcast(new MessageSent($message));

        return $message->load('sender:id,name,avatar');
    }

    public function sendForNewExchange(User $sender, int $receiverId, int $exchangeId, string $content): Message
    {
        return Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiverId,
            'exchange_id' => $exchangeId,
            'content' => $content,
            'is_read' => false,
        ]);
    }
}
