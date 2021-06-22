<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\Log;

use App\Models\User;

class UserRepository
{
    public function getOrSaveUser(string $nickname): int
    {
        try {
            $user = User::firstOrCreate([
                'nickname' => $nickname
            ]);

            return $user->id;
        } catch (Exception $exception) {
            Log::error($exception);
            return 0;
        }
    }
}
