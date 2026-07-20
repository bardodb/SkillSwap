<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(User $user, Message $message): bool
    {
        return $this->isParticipant($user, $message);
    }

    public function update(User $user, Message $message): bool
    {
        return (int) $message->receiver_id === (int) $user->id;
    }

    public function delete(User $user, Message $message): bool
    {
        return (int) $message->sender_id === (int) $user->id;
    }

    private function isParticipant(User $user, Message $message): bool
    {
        return (int) $message->sender_id === (int) $user->id
            || (int) $message->receiver_id === (int) $user->id;
    }
}
