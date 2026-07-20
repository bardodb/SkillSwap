<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function update(User $user, Skill $skill): bool
    {
        return (int) $skill->user_id === (int) $user->id;
    }

    public function delete(User $user, Skill $skill): bool
    {
        return (int) $skill->user_id === (int) $user->id;
    }
}
