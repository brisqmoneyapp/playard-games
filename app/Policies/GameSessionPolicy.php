<?php

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->role === 'super_admin') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin', 'staff'], true);
    }

    public function view(User $user, GameSession $gameSession): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin', 'staff'], true);
    }

    public function create(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin', 'staff'], true);
    }

    public function update(User $user, GameSession $gameSession): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin', 'staff'], true);
    }

    public function delete(User $user, GameSession $gameSession): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin'], true);
    }
}