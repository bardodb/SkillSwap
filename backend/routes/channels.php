<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{min}.{max}', function ($user, int $min, int $max) {
    if ($min > $max) {
        return false;
    }

    return (int) $user->id === $min || (int) $user->id === $max;
});
