<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Repositories\QuestionRepository;
use App\Repositories\StatRepository;

class StatService
{
    private $questionRepository;
    private $statsRepository;

    public function __construct(
        QuestionRepository $questionRepository,
        StatRepository $statsRepository
    )
    {
        $this->questionRepository = $questionRepository;
        $this->statsRepository = $statsRepository;
    }

    public function getQuestionsStats(int $userId): array
    {
        try {
            $questions = $this->questionRepository->getQuestions();
            $numberQuestions = $questions->count();

            if (!session()->has('number_questions')) {
                session(['number_questions' => $numberQuestions]);
            }

            if ($numberQuestions == 0) {
                return [
                    'error_message' => 'You have to save at least one question to be able to practice!'
                ];
            }

            $stats = $this->statsRepository->getStatsByUser($userId);

            $arrayQuestionsStats = array();

            foreach ($questions as $key => $question) {
                $statExists = false;
                $correctlyAnswered = false;
                foreach ($stats as $stat) {
                    if ($stat['question_id'] == $question->id) {
                        $statExists = true;
                        if ($stat['correctly_answered']) {
                            $correctlyAnswered = true;
                        }
                    }
                }

                if (!$statExists) {
                    $statusQuestion = 'Not answered';
                } elseif (!$correctlyAnswered) {
                    $statusQuestion = 'Incorrect';
                } else {
                    $statusQuestion = 'Correct';
                }
                $arrayQuestionsStats['question_status'][$key]['id'] = $question->id;
                $arrayQuestionsStats['question_status'][$key]['question'] = $question->question;
                $arrayQuestionsStats['question_status'][$key]['status'] = $statusQuestion;

                $arrayQuestionsStats['questions_ids'][] = $question->id;
            }

            $userStats = $this->getStats($numberQuestions, $stats);

            if (count($userStats) == 0) {
                return [
                    'error_message' => 'Something went wrong when opening the stats!'
                ];
            }

            $percentageAnsweredQuestions = $userStats[0]['correctly_answered'];

            return [
                'question_status' => $arrayQuestionsStats['question_status'],
                'questions_ids' => $arrayQuestionsStats['questions_ids'],
                'user_stats' => $userStats,
                'number_questions' => $numberQuestions,
                'percentage_answered_questions' => $percentageAnsweredQuestions
            ];
        } catch (Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function getStats(
        int $numberOfQuestions,
        array $stats
    ): array
    {
        try {
            $answered = count($stats);

            if ($answered == 0) {
                return [[
                    'number_questions' => $numberOfQuestions,
                    'answered' => '0%',
                    'correctly_answered' => '0%'
                ]];
            }

            $correctlyAnswered = 0;
            foreach ($stats as $stat) {
                if ($stat['correctly_answered']) {
                    $correctlyAnswered++;
                }
            }

            return [[
                'number_questions' => $numberOfQuestions,
                'answered' => round(($answered / $numberOfQuestions) * 100, 1) . '%',
                'correctly_answered' => round(($correctlyAnswered / $numberOfQuestions) * 100, 1) . '%'
            ]];
        } catch (Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function saveStat(
        int $userId,
        int $questionId,
        bool $correctlyAnswered
    ): bool
    {
        try {
            DB::beginTransaction();
            $saveStat = $this->statsRepository->store($userId, $questionId, $correctlyAnswered);
            if (!$saveStat) {
                DB::rollBack();
                return false;
            }

            DB::commit();
            return true;
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            return false;
        }
    }

    public function getCompleteStatsByUser(int $userId): array
    {
        try {
            if (!session()->has('number_questions')) {
                $numberOfQuestions = $this->questionRepository->getQuestions()->count();
                session(['number_questions' => $numberOfQuestions]);
            } else {
                $numberOfQuestions = session('number_questions');
            }

            $stats = $this->statsRepository->getStatsByUser($userId);
            $answered = count($stats);

            if (count($stats) == 0) {
                return [[
                    'number_questions' => $numberOfQuestions,
                    'answered' => '0%',
                    'correctly_answered' => '0%'
                ]];
            }
            $correctlyAnswered = 0;
            foreach ($stats as $stat) {
                if ($stat['correctly_answered']) {
                    $correctlyAnswered++;
                }
            }

            return [[
                'number_questions' => $numberOfQuestions,
                'answered' => round(($answered / $numberOfQuestions) * 100, 1) . '%',
                'correctly_answered' => round(($correctlyAnswered / $numberOfQuestions) * 100, 1) . '%'
            ]];
        } catch (Exception $exception) {
            Log::error($exception);
            return [
                'error_message' => 'Something went wrong when opening the stats!'
            ];
        }
    }

    public function resetStats(int $userId): array
    {
        try {
            $resetStatus = $this->statsRepository->resetStats($userId);

            if ($resetStatus) {
                $message = 'Stats has been reset!';
            } else {
                $message = 'Something went wrong when resetting the stats!';
            }

            return [
                'message' => $message
            ];
        } catch (Exception $exception) {
            Log::error($exception);
            return [
                'message' => 'Something went wrong when resetting the stats!'
            ];
        }
    }
}
