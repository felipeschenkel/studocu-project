<?php

namespace App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

use App\Models\Question;

class QuestionRepository
{
    public function saveQuestion(string $question): int
    {
        try {
            $question = Question::create([
                'question' => $question
            ]);

            if (session()->has('number_questions')) {
                $numberOfQuestions = session('number_questions');
                session(['number_questions' => $numberOfQuestions + 1]);
            }

            return $question->id;
        } catch (Exception $exception) {
            Log::error($exception);
            return 0;
        }
    }

    public function getQuestions(): Collection
    {
        return Question::all();
    }

    public function getQuestion(int $questionId): object
    {
        return Question::find($questionId);
    }
}
