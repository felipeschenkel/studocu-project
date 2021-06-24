<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

use App\Models\Stat;

class StatRepository
{
    public function saveStat(
        int $userId,
        int $questionId,
        bool $correctlyAnswered = false
    ): object
    {
        return Stat::updateOrCreate(
            ['user_id' => $userId, 'question_id' => $questionId],
            ['correctly_answered' => $correctlyAnswered]
        );
    }

    public function resetStats(int $userId): void
    {
        DB::table('stats')
            ->where('user_id', $userId)
            ->delete();
    }
}
