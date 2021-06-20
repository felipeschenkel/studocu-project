<?php

namespace App\Repositories;

use App\Models\Answer;
use Illuminate\Support\Facades\Log;

class AnswerRepository
{
    public function store(
        int $questionId,
        string $answer,
        bool $correctAnswer
    ): bool
    {
        try {
            Answer::create([
                'question_id' => $questionId,
                'answer' => $answer,
                'is_correct' => $correctAnswer
            ]);

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }

    public function getAnswersByQuestion(int $questionId): object
    {
        return Answer::where('question_id', $questionId)
            ->get();
    }
}
