<?php

namespace App\Services;

use App\Models\Stat;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Question;
use App\Repositories\StatRepository;

class StatService
{
    private $statRepository;

    public function __construct(StatRepository $statRepository)
    {
        $this->statRepository = $statRepository;
    }

    public function getQuestionsStats(int $userId): array
    {
        try {
            $questions = (new Question())->getQuestions();
            $numberQuestions = $questions->count();

            if ($numberQuestions == 0) {
                return [
                    'error_message' => 'You have to save at least one question to be able to practice!'
                ];
            }

            $stats = (new Stat())->getStatsByUser($userId);

            $arrayQuestionsStats = array();

            foreach ($questions as $key => $question) {
                $statExists = false;
                $correctlyAnswered = false;
                foreach ($stats as $stat) {
                    if ($stat->question_id == $question->id) {
                        $statExists = true;
                        if ($stat->correctly_answered) {
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
        object $stats
    ): array
    {
        try {
            $answered = $stats->count();

            if ($answered == 0) {
                return [[
                    'number_questions' => $numberOfQuestions,
                    'answered' => '0%',
                    'correctly_answered' => '0%'
                ]];
            }

            $correctlyAnswered = 0;
            foreach ($stats as $stat) {
                if ($stat->correctly_answered) {
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
            $objSaveStat = $this->statRepository->saveStat($userId, $questionId, $correctlyAnswered);
            if (!isset($objSaveStat->id)) {
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
            $numberOfQuestions = (new Question())->getQuestions()->count();

            $stats = (new Stat())->getStatsByUser($userId);
            $answered = $stats->count();

            if ($answered == 0) {
                return [[
                    'number_questions' => $numberOfQuestions,
                    'answered' => '0%',
                    'correctly_answered' => '0%'
                ]];
            }
            $correctlyAnswered = 0;
            foreach ($stats as $stat) {
                if ($stat->correctly_answered) {
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
            $this->statRepository->resetStats($userId);

            return ['message' => 'Stats has been reset!'];
        } catch (Exception $exception) {
            Log::error($exception);
            return [
                'message' => 'Something went wrong when resetting the stats!'
            ];
        }
    }
}
