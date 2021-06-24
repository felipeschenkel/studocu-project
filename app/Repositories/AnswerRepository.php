<?php

namespace App\Repositories;

use App\Models\Answer;

class AnswerRepository
{
    public function saveAnswer(
        int $questionId,
        string $answer,
        bool $correctAnswer
    ): object
    {
        return Answer::create([
            'question_id' => $questionId,
            'answer' => $answer,
            'is_correct' => $correctAnswer
        ]);
    }
}
