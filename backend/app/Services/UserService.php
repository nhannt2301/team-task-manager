<?php

namespace App\Services;

use App\Constants\CUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    /**
     * @return Collection<int, User>
     */
    public function listAssignees(): Collection
    {
        return User::query()
            ->where('role', CUser::ROLE_USER)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
