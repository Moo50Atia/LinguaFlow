<?php

namespace App\Policies;

use App\Models\Moment;
use App\Models\User;

class MomentPolicy
{
    /**
     * Determine if the user can delete the moment.
     */
    public function delete(User $user, Moment $moment): bool
    {
        return $moment->user_id === $user->id || $user->role === 'admin';
    }
}
