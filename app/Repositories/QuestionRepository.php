<?php

namespace App\Repositories;

use App\Models\Question;
use Illuminate\Support\Facades\Log;

class QuestionRepository
{
    public function store(
        string $question,
        int $userId,
        bool $multipleChoice
    ): int
    {
        try {
            $question = Question::create([
                'user_id' => $userId,
                'question' => $question,
                'multiple_choice' => $multipleChoice
            ]);

            return $question->id;
        } catch (\Exception $exception) {
            Log::error($exception);
            return 0;
        }
    }

    public function delete(int $questionId): bool
    {
        try {
            $question = Question::find($questionId);
            $question->delete();

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }

    public function getUserQuestions(int $userId): Object
    {
        return Question::where('user_id', $userId)
            ->get();
    }

    public function getNumberQuestions(int $userId): int
    {
        return Question::where('user_id', $userId)
            ->count();
    }

    public function getUserQuestionsAndStatus(int $userId): object
    {
        return Question::where('user_id', $userId)
            ->get();
    }

    public function getQuestion(int $questionId): object
    {
        return Question::find($questionId);
    }
}
