<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Repositories\AnswerRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\StatsRepository;
use Illuminate\Support\Facades\Log;

class QuestionController extends BaseController
{
    private $questionRepository;
    private $answerRepository;
    private $statRepository;

    public function __construct(
        AnswerRepository $answerRepository,
        QuestionRepository $questionRepository,
        StatsRepository $statRepository
    )
    {
        $this->questionRepository = $questionRepository;
        $this->answerRepository = $answerRepository;
        $this->statRepository = $statRepository;
    }

    public function store(
        string $question,
        int $userId,
        bool $multipleChoice
    ): int
    {
        try {
            $questionId = $this->questionRepository->store($question, $userId, $multipleChoice);
            $this->statRepository->store($userId, $questionId);

            return $questionId;
        } catch (\Exception $exception) {
            Log::error($exception);
            return 0;
        }
    }

    public function deleteQuestion(int $questionId): bool
    {
        return $this->questionRepository->delete($questionId);
    }

    public function getListQuestionsAndAnswers(int $userId): array
    {
        try {
            $questions = $this->questionRepository->getUserQuestions($userId);
            $arrayQuestionsAndAnswers = array();
            foreach ($questions as $keyQuest => $question) {
                $answers = $this->answerRepository->getAnswersByQuestion($question->id);

                $stringAnswer = '';
                foreach ($answers as $keyAnswer => $answer) {
                    if ($question->multiple_choice) {
                        if ($answer->is_correct) {
                            $correctAnswer = $keyAnswer + 1;
                        }
                        $stringAnswer .= ($keyAnswer + 1) . ')' . $answer->answer . ' ';
                    } else {
                        if ($answer->is_correct) {
                            $correctAnswer = $answer->answer;
                        }
                        $stringAnswer .= $answer->answer;
                    }
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

    public function getQuestionsStats(int $userId): array
    {
        try {
            $questions = $this->questionRepository->getUserQuestionsAndStatus($userId);
            $arrayQuestionsStats = array();

            foreach ($questions as $key => $question) {
                $statusQuestion = '';
                $arrayQuestionsStats[$key]['id'] = $question->id;
                $arrayQuestionsStats[$key]['question'] = $question->question;
                $getStatByQuestion = $this->statRepository->getStatByQuestion($question->id, $userId);

                if (!$getStatByQuestion->answered) {
                    $statusQuestion = 'Not answered';
                } elseif (!$getStatByQuestion->correctly_answered) {
                    $statusQuestion = 'Incorrect';
                } elseif ($getStatByQuestion->correctly_answered) {
                    $statusQuestion = 'Correct';
                }
                $arrayQuestionsStats[$key]['status'] = $statusQuestion;
            }

            return $arrayQuestionsStats;
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function getQuestionAndAnswers(int $questionId): array
    {
        try {
            $selQuestion = $this->questionRepository->getQuestion($questionId);
            $answers = $this->answerRepository->getAnswersByQuestion($questionId);

            $arrayQuestionAndAnswer = array();
            $arrayQuestionAndAnswer['multiple_choice'] = false;
            $arrayQuestionAndAnswer['question'] = $selQuestion->question;
            $arrayQuestionAndAnswer['questionId'] = $questionId;

            if ($selQuestion->multiple_choice) {
                $arrayQuestionAndAnswer['multiple_choice'] = true;
                foreach ($answers as $key => $answer) {
                    $arrayQuestionAndAnswer['answers'][$key] = $answer->answer;
                    if ($answer->is_correct) {
                        $arrayQuestionAndAnswer['correct_answer'] = $answer->answer;
                    }
                }
            } else {
                $arrayQuestionAndAnswer['correct_answer'] = $answers->first()->answer;
                $arrayQuestionAndAnswer['answer'] = $answers->first()->answer;
            }

            return $arrayQuestionAndAnswer;
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

}
