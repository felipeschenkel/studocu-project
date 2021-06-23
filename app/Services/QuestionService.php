<?php

namespace App\Services;

use App\Models\Answer;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Question;
use App\Repositories\QuestionRepository;

class QuestionService
{
    private $questionRepository;
    private $answerService;

    public function __construct(
        QuestionRepository $questionRepository,
        AnswerService $answerService
    )
    {
        $this->questionRepository = $questionRepository;
        $this->answerService = $answerService;
    }

    public function saveQuestionAndAnswers(
        string $question,
        string $correctAnswer,
        array $answers
    ): array
    {
        try {
            DB::beginTransaction();

            $questionId = $this->questionRepository->saveQuestion($question);
            if ($questionId == 0) {
                DB::rollBack();
                return ['message' => 'Problem saving the question'];
            }

            $saveAnswers = $this->answerService->saveAnswers($questionId, $correctAnswer, $answers);
            if (!$saveAnswers) {
                DB::rollBack();
                return ['message' => 'Problem saving the answers'];
            }

            DB::commit();

            return ['message' => 'Question saved successfully!'];
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return ['message' => 'An error occurred while performing this action'];
        }
    }

    public function getListQuestionsAndAnswers(): array
    {
        try {
            $questions = (new Question())->getQuestions();
            $arrayQuestionsAndAnswers = array();

            foreach ($questions as $keyQuest => $question) {
                $answers = (new Answer())->getAnswersByQuestion($question->id);

                $stringAnswer = '';
                foreach ($answers as $keyAnswer => $answer) {
                    if ($answer->is_correct) {
                        $correctAnswer = $keyAnswer + 1;
                    }
                    $stringAnswer .= ($keyAnswer + 1) . ')' . $answer->answer . ' ';
                }

                $arrayQuestionsAndAnswers[$keyQuest]['question'] = ($keyQuest + 1) . ') ' . $question->question;
                $arrayQuestionsAndAnswers[$keyQuest]['answer'] = $stringAnswer;
                $arrayQuestionsAndAnswers[$keyQuest]['correctAnswer'] = $correctAnswer ?? 'Not found';
            }

            return $arrayQuestionsAndAnswers;
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function getQuestionValidation(int $questionToAnswer, array $arrQuestions): array
    {
        $questionExists = false;
        $questionAlreadyAnsweredCorrectly = false;

        foreach ($arrQuestions['question_status'] as $arrayQuestionStat) {
            if ($questionToAnswer == $arrayQuestionStat['id']) {
                $questionExists = true;
                if ($arrayQuestionStat['status'] == 'Correct') {
                    $questionAlreadyAnsweredCorrectly = true;
                }
            }
        }

        if (!$questionExists) {
            $errorMessage = 'The Id entered does not exist';
        }
        if ($questionAlreadyAnsweredCorrectly) {
            $errorMessage = 'This question has already been answered correctly';
        }

        return [
            'error_message' => $errorMessage ?? ''
        ];
    }

    public function getQuestionAndAnswers(int $questionId): array
    {
        try {
            $selQuestion = (new Question())->getQuestion($questionId);
            $answers = (new Answer())->getAnswersByQuestion($questionId);

            $arrayQuestionAndAnswer = array();
            $arrayQuestionAndAnswer['question'] = $selQuestion->question;
            $arrayQuestionAndAnswer['questionId'] = $questionId;

            foreach ($answers as $key => $answer) {
                $arrayQuestionAndAnswer['answers'][$key] = $answer->answer;
                if ($answer->is_correct) {
                    $arrayQuestionAndAnswer['correct_answer'] = $answer->answer;
                }
            }

            if (count($arrayQuestionAndAnswer) == 0) {
                return [
                    'error_message' => 'Problem while opening the question.'
                ];
            }

            return $arrayQuestionAndAnswer;
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }
}
