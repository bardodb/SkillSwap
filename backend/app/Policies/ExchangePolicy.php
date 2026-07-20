<?php

namespace App\Policies;

use App\Models\Exchange;
use App\Models\User;

class ExchangePolicy
{
    public function view(User $user, Exchange $exchange): bool
    {
        return $this->isParticipant($user, $exchange);
    }

    public function update(User $user, Exchange $exchange): bool
    {
        return $this->isParticipant($user, $exchange);
    }

    public function delete(User $user, Exchange $exchange): bool
    {
        return $this->isParticipant($user, $exchange);
    }

    private function isParticipant(User $user, Exchange $exchange): bool
    {
        return (int) $exchange->initiator_id === (int) $user->id
            || (int) $exchange->receiver_id === (int) $user->id;
    }
}
