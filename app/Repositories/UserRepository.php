<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getOrSaveUser(string $nickname): object
    {
        return User::firstOrCreate([
            'nickname' => $nickname
        ]);
    }
}
