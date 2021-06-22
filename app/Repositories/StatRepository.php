<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Stat;

class StatRepository
{
    public function store(
        int $userId,
        int $questionId,
        bool $correctlyAnswered = false
    ): bool
    {
        try {
            Stat::updateOrCreate(
                ['user_id' => $userId, 'question_id' => $questionId],
                ['correctly_answered' => $correctlyAnswered]
            );

            return true;
        } catch (Exception $exception) {
            Log::error($exception);
            return false;
        }
    }

    public function getStatsByUser(int $userId): array
    {
        return Stat::where('user_id', $userId)
            ->get()
            ->toArray();
    }

    public function resetStats(int $userId): bool
    {
        try {
            DB::table('stats')
                ->where('user_id', $userId)
                ->delete();

            return true;
        } catch (Exception $exception) {
            Log::error($exception);
            return false;
        }
    }
}
