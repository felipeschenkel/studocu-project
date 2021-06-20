<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Repositories\QuestionRepository;
use App\Repositories\StatsRepository;
use Illuminate\Support\Facades\Log;

class StatsController extends BaseController
{
    private $statsRepository;
    private $questionRepository;

    public function __construct(
        StatsRepository $statsRepository,
        QuestionRepository $questionRepository
    )
    {
        $this->statsRepository = $statsRepository;
        $this->questionRepository = $questionRepository;
    }

    public function getStatsByUser(int $userId): array
    {
        try {
            $numberOfQuestions = $this->questionRepository->getNumberQuestions($userId);
            $stats = $this->statsRepository->getStatsByUser($userId);

            if (count($stats) == 0) {
                return [[
                    'number_questions' => $numberOfQuestions,
                    'answered' => '0%',
                    'correctly_answered' => '0%'
                ]];
            }
            $answered = 0;
            $correctlyAnswered = 0;
            foreach ($stats as $stat) {
                if ($stat['answered']) {
                    $answered++;
                }
                if ($stat['correctly_answered']) {
                    $correctlyAnswered++;
                }
            }

            return [[
                'number_questions' => $numberOfQuestions,
                'answered' => round(($answered / $numberOfQuestions) * 100, 1) . '%',
                'correctly_answered' => round(($correctlyAnswered / $numberOfQuestions) * 100, 1) . '%'
            ]];
        } catch (\Exception $exception) {
            Log::error($exception);
            return [];
        }
    }

    public function store(
        int $userId,
        int $questionId,
        bool $correctlyAnswered,
        bool $answered
    ): bool
    {
        try {
            $this->statsRepository->store($userId, $questionId, $correctlyAnswered, $answered);

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }

    public function resetStats(int $userId): bool
    {
        try {
            $this->statsRepository->deleteStats($userId);

            return true;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }
}
