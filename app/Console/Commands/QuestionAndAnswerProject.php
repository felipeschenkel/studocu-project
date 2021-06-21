<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\UserController;

class QuestionAndAnswerProject extends Command
{
    protected $signature = 'qanda:interactive';

    protected $description = 'Questions and Answers Project';

    private $choices;
    private $userController;
    private $questionController;
    private $answerController;
    private $statsController;
    private $userId;

    public function __construct(
        UserController $userController,
        QuestionController $questionController,
        AnswerController $answerController,
        StatsController $statsController
    )
    {
        parent::__construct();

        $this->choices = [
            1 => 'Create a question',
            2 => 'List all questions',
            3 => 'Practice',
            4 => 'Stats',
            5 => 'Reset',
            6 => 'Exit'
        ];

        $this->userController = $userController;
        $this->questionController = $questionController;
        $this->answerController = $answerController;
        $this->statsController = $statsController;
        $this->userId = 0;
    }

    public function handle()
    {
        $nickname = $this->ask('What is your nickname?');

        $this->userId = $this->userController->getOrSaveUser($nickname);

        if ($this->userId == 0) {
            echo 'Error creating or retrieving the user - probably there is a problem with your DB connection';
            exit;
        }

        $this->initialMenu();
    }

    public function initialMenu(): void
    {
        $this->newLine(5);
        $this->info('Welcome to the Questions and Answers Project');
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
            echo 'Goodbye!';
            exit;
        }
    }

    public function createQuestion(): void
    {
        try {
            $question = $this->ask('Please, type the question:');

            $multipleChoice = $this->choice('I need to know, is it a multiple choice question?', [
                'Yes', 'No'
            ]);

            if ($multipleChoice == 'Yes') {
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
                $indexCorrectAnswer = array_search($correctAnswer, $arrayAnswers);

                $questionId = $this->questionController->store($question, $this->userId, true);

                if ($questionId == 0) {
                    $this->line('Something went wrong! Sorry!');
                    $this->initialMenu();
                }

                $saveAnswers = $this->answerController->store($questionId, $arrayAnswers, $indexCorrectAnswer);

                if ($saveAnswers) {
                    $this->line('Question saved!');
                } else {
                    $this->line('Something went wrong when saving the answers!');
                    $this->questionController->deleteQuestion($questionId);
                }
                $this->initialMenu();
            }

            if ($multipleChoice == 'No') {
                $answer = $this->ask('Please, type the answer:');

                $questionId = $this->questionController->store($question, $this->userId, false);

                if ($questionId == 0) {
                    $this->line('Something went wrong! Sorry!');
                    $this->initialMenu();
                }

                $saveAnswer = $this->answerController->store($questionId, [$answer], true);

                if ($saveAnswer) {
                    $this->line('Question saved!');
                } else {
                    $this->line('Something went wrong when saving the answer!');
                    $this->questionController->deleteQuestion($questionId);
                }
                $this->initialMenu();
            }

            exit;
        } catch (\Exception $exception) {
            Log::error($exception);
            $this->line('Something went wrong!');
            exit;
        }
    }

    public function listQuestions(): void
    {
        $arrayQuestionsAndAnswers = $this->questionController->getListQuestionsAndAnswers($this->userId);

        $this->table(
            ['Questions', 'Answers', 'Correct Answer'],
            $arrayQuestionsAndAnswers
        );

        $this->initialMenu();
    }

    public function openPractice(): void
    {
        $arrayQuestionsStats = $this->questionController->getQuestionsStats($this->userId);
        $arrayStats = $this->statsController->getStatsByUser($this->userId);

        if (count($arrayQuestionsStats) == 0) {
            $this->line('You have to save at least one question to be able to practice!');
            $this->initialMenu();
        }

        if (count($arrayStats) == 0) {
            $this->line('Something went wrong when opening the stats!');
            $this->initialMenu();
        }

        $this->table(
            ['Id Question', 'Question', 'Status'],
            $arrayQuestionsStats
        );

        $numberOfQuestions = $arrayStats[0]['number_questions'];
        $percentageQuestionsAnswered = $arrayStats[0]['correctly_answered'];

        $this->line('There is ' . $numberOfQuestions . ' question(s) registered');
        $this->line('You completed ' . $percentageQuestionsAnswered);

        $questionToAnswer = $this->ask('Please, type the Id of the question that you want to answer or type exit to go to the main menu:');

        if (strtolower($questionToAnswer) == 'exit') {
            $this->initialMenu();
        }

        $questionExists = false;
        $questionAlreadyAnsweredCorrectly = false;

        foreach ($arrayQuestionsStats as $arrayQuestionStat) {
            $questionId = $arrayQuestionStat['id'];
            if ($questionToAnswer == $questionId) {
                $questionExists = true;
                if ($arrayQuestionStat['status'] == 'Correct') {
                    $questionAlreadyAnsweredCorrectly = true;
                }
            }
        }

        if (!$questionExists) {
            $this->line("The Id entered does not exist.");
            $this->openPractice();
        }

        if ($questionAlreadyAnsweredCorrectly) {
            $this->line("This question has already been answered correctly.");
            $this->openPractice();
        }

        $this->makeQuestion($questionToAnswer);
    }

    public function makeQuestion(int $questionToAnswer)
    {
        $selQuestionAndItsAnswers = $this->questionController->getQuestionAndAnswers($questionToAnswer);

        if (count($selQuestionAndItsAnswers) == 0) {
            $this->line("Problem while opening the question.");
            $this->openPractice();
        }

        if ($selQuestionAndItsAnswers['multiple_choice']) {
            $question = $this->choice($selQuestionAndItsAnswers['question'], $selQuestionAndItsAnswers['answers']);
        } else {
            $question = $this->ask($selQuestionAndItsAnswers['question']);
        }

        if ($question == $selQuestionAndItsAnswers['correct_answer']) {
            $this->line('Correct! Congratulations!');
            $this->statsController->store(
                $this->userId,
                $selQuestionAndItsAnswers['questionId'],
                true,
                true
            );
        } else {
            $this->line('Wrong answer!');
            $this->statsController->store(
                $this->userId,
                $selQuestionAndItsAnswers['questionId'],
                false,
                true
            );
        }

        $this->openPractice();
    }

    public function openStats(): void
    {
        $arrayStats = $this->statsController->getStatsByUser($this->userId);

        if (count($arrayStats) == 0) {
            $this->line('Something went wrong when opening the stats!');
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
        $resetStats = $this->statsController->resetStats($this->userId);

        if ($resetStats) {
            $this->line('Stats has been reset!');
        } else {
            $this->line('Something went wrong when resetting the stats!');
        }

        $this->initialMenu();
    }

}
