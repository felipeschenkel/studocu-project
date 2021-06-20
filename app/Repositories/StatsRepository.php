<?php

namespace App\Repositories;

use App\Models\Stats;
use Illuminate\Support\Facades\Log;

class StatsRepository
{
    public function store(
        int $userId,
        int $questionId,
        bool $correctlyAnswered = false,
        bool $answered = false
    ): bool
    {
        try {
            Stats::updateOrCreate(
                ['user_id' => $userId, 'question_id' => $questionId],
                ['answered' => $answered, 'correctly_answered' => $correctlyAnswered]
            );

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }

    public function getStatsByUser(int $userId): array
    {
        return Stats::where('user_id', $userId)
            ->get()
            ->toArray();
    }

    public function deleteStats(int $userId): bool
    {
        try {
            Stats::where('user_id', $userId)
                ->update([
                    'answered' => false,
                    'correctly_answered' => false
                ]);

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }

    public function getStatByQuestion(int $questionId, int $userId): object
    {
        return Stats::where('question_id', $questionId)
            ->where('user_id', $userId)
            ->first();
    }
}
