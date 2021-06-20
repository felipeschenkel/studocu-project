<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Repositories\AnswerRepository;
use Illuminate\Support\Facades\Log;

class AnswerController extends BaseController
{
    private $answerRepository;

    public function __construct(AnswerRepository $answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    public function store(
        int $questionId,
        array $answers,
        int $indexCorrectAnswer
    ): bool
    {
        try {
            if (count($answers) == 1) {
                return $this->answerRepository->store($questionId, $answers[0], true);
            }

            foreach ($answers as $key => $answer) {
                $correctAnswer = false;
                if ($key == $indexCorrectAnswer) {
                    $correctAnswer = true;
                }
                $this->answerRepository->store($questionId, $answer, $correctAnswer);
            }

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }
}
