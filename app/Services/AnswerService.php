<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Repositories\AnswerRepository;

class AnswerService
{
    private $answerRepository;

    public function __construct(AnswerRepository $answerRepository)
    {
        $this->answerRepository = $answerRepository;
    }

    public function saveAnswers(
        int $questionId,
        string $correctAnswer,
        array $answers
    ): bool
    {
        try {
            DB::beginTransaction();
            $indexCorrectAnswer = array_search($correctAnswer, $answers);
            foreach ($answers as $key => $answer) {
                $correctAnswer = ($key == $indexCorrectAnswer);
                $objSaveAnswer = $this->answerRepository->saveAnswer($questionId, $answer, $correctAnswer);
                if (!isset($objSaveAnswer->id)) {
                    DB::rollBack();
                    return false;
                }
            }

            DB::commit();

            return true;
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return false;
        }
    }
}
