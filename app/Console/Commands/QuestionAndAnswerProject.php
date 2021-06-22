<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Services\AnswerService;
use App\Services\QuestionService;
use App\Services\StatService;
use App\Services\UserService;

class QuestionAndAnswerProject extends Command
{
    protected $signature = 'qanda:interactive';

    protected $description = 'Questions and Answers Project';

    private array $choices;
    private string $nickname;
    private int $userId;

    private $answerService;
    private $questionService;
    private $statService;
    private $userService;

    public function __construct(
        AnswerService $answerService,
        QuestionService $questionService,
        StatService $statService,
        UserService $userService
    )
    {
        parent::__construct();

        $this->choices = [
            1 => 'Create a question',
            2 => 'List all questions',
            3 => 'Practice',
            4 => 'Stats',
            5 => 'Reset',
            6 => 'Change user',
            7 => 'Exit'
        ];

        $this->answerService = $answerService;
        $this->questionService = $questionService;
        $this->statService = $statService;
        $this->userService = $userService;
        $this->userId = 0;
        $this->nickname = '';
    }

    public function handle()
    {
        $this->askNickname();
    }

    public function askNickname(): void
    {
        $nickname = $this->ask('What is your nickname?');

        $saveUser = $this->userService->getOrSaveUser($nickname);

        if (isset($saveUser['error'])) {
            $this->info($saveUser['error']);
            $this->askNickname();
        }

        $this->nickname = $saveUser['sanitized_nickname'];
        $this->userId = $saveUser['user_id'];

        $this->initialMenu();
    }

    public function initialMenu(): void
    {
        $this->newLine(5);
        $this->info('Hello ' .$this->nickname. ', Welcome to the Questions and Answers Project');
        $menuChoice = $this->choice('What do you want to do?', $this->choices);
        $optionSelected = array_search($menuChoice, $this->choices);

        if ($optionSelected == 1) {
            $this->createQuestion();
        }

        if ($optionSelected == 2) {
            $this->listQuestions();
        }

        if ($optionSelected == 3) {
            $this->openPractice();
        }

        if ($optionSelected == 4) {
            $this->openStats();
        }

        if ($optionSelected == 5) {
            $this->resetStats();
        }

        if ($optionSelected == 6) {
            $this->askNickname();
        }

        if ($optionSelected == 7) {
            echo 'Goodbye '.$this->nickname.'!';
            exit;
        }
    }

    public function createQuestion(): void
    {
        try {
            $question = $this->ask('Please, type the question:');

            $arrayAnswers = array();
            $numberOfAnswers = $this->choice("How many answers will this question have?", [
                2 => '2',
                3 => '3',
                4 => '4'
            ]);

            for ($i = 0; $i < $numberOfAnswers; $i++) {
                $answer = $this->ask('Please, type the answer ' . ($i + 1));
                $arrayAnswers[$i+1] = $answer;
            }

            $correctAnswer = $this->choice('Select the correct answer for your question', $arrayAnswers);

            $saveQuestionAndAnswers = $this->questionService->saveQuestionAndAnswers(
                $question,
                $correctAnswer,
                $arrayAnswers
            );

            $this->info($saveQuestionAndAnswers['message']);
            $this->initialMenu();
        } catch (Exception $exception) {
            Log::error($exception);
            $this->line('Something went wrong!');
            exit;
        }
    }

    public function listQuestions(): void
    {
        $arrayQuestionsAndAnswers = $this->questionService->getListQuestionsAndAnswers();

        $this->table(
            ['Questions', 'Answers', 'Correct Answer'],
            $arrayQuestionsAndAnswers
        );

        $this->initialMenu();
    }

    public function openPractice(): void
    {
        try {
            $arrQuestions = $this->statService->getQuestionsStats($this->userId);
            $numberOfQuestions = $arrQuestions['number_questions'];
            $percentageQuestionsAnswered = $arrQuestions['percentage_answered_questions'];

            if (isset($arrQuestions['error_message'])) {
                $this->line($arrQuestions['error_message']);
                $this->initialMenu();
            }

            $this->table(
                ['Id Question', 'Question', 'Status'],
                $arrQuestions['question_status']
            );

            $this->line('There is ' . $numberOfQuestions . ' question(s) registered');
            $this->line('You completed ' . $percentageQuestionsAnswered);

            $questionToAnswer = $this->ask('Please, type the Id of the question that you want to answer or type exit to go to the main menu:');

            if (strtolower($questionToAnswer) == 'exit') {
                $this->initialMenu();
            }

            if (!in_array($questionToAnswer, $arrQuestions['questions_ids'])) {
                $this->info('Invalid Id');
                $this->openPractice();
            }

            $questionValidation = $this->questionService->getQuestionValidation($questionToAnswer, $arrQuestions);

            if (!empty($questionValidation['error_message'])) {
                $this->info($questionValidation['error_message']);
                $this->openPractice();
            }

            $this->makeQuestion($questionToAnswer);
        } catch (Exception $exception) {
            Log::error($exception);
            $this->line('Something went wrong!');
            $this->openPractice();
        }
    }

    public function makeQuestion(int $questionToAnswer): void
    {
        try {
            $selQuestionAndItsAnswers = $this->questionService->getQuestionAndAnswers($questionToAnswer);

            if (isset($selQuestionAndItsAnswers['error_message'])) {
                $this->line($selQuestionAndItsAnswers['error_message']);
                $this->openPractice();
            }

            $question = $this->choice(
                $selQuestionAndItsAnswers['question'],
                $selQuestionAndItsAnswers['answers']
            );

            if ($selQuestionAndItsAnswers['correct_answer'] == $question) {
                $this->line('Correct! Congratulations!');
            } else {
                $this->line('Wrong answer!');
            }

            $this->statService->saveStat(
                $this->userId,
                $selQuestionAndItsAnswers['questionId'],
                ($selQuestionAndItsAnswers['correct_answer'] == $question)
            );

            $this->openPractice();
        } catch (Exception $exception) {
            Log::error($exception);
            $this->line('Something went wrong!');
            $this->openPractice();
        }
    }

    public function openStats(): void
    {
        $arrayStats = $this->statService->getCompleteStatsByUser($this->userId);

        if (isset($arrayStats['error_message'])) {
            $this->line($arrayStats['error_message']);
            $this->initialMenu();
        }

        $this->table(
            ['Total Questions', 'Questions Answered', 'Questions Correctly Answered'],
            $arrayStats
        );

        $this->initialMenu();
    }

    public function resetStats(): void
    {
        $resetStats = $this->statService->resetStats($this->userId);
        $this->line($resetStats['message']);
        $this->initialMenu();
    }
}
