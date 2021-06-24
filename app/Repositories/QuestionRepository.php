<?php

namespace App\Repositories;

use App\Models\Question;

class QuestionRepository
{
    public function saveQuestion(string $question): object
    {
        return Question::create([
            'question' => $question
        ]);
    }
}
