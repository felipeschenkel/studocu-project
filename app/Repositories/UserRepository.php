<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function getOrSaveUser (string $nickname): int
    {
        try {
            $user = User::firstOrCreate([
                'nickname' => $nickname
            ]);

            return $user->id;
        } catch (\Exception $exception) {
            Log::error($exception);
            return 0;
        }
    }
}
