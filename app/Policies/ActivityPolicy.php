<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->is_active) {
            return false;
        }

        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin', 'staff'], true);
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin', 'staff'], true);
    }

    public function create(User $user): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin'], true);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->is_active && in_array($user->role, ['super_admin', 'admin'], true);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->is_active && $user->role === 'super_admin';
    }
}