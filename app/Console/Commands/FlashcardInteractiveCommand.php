<?php

namespace App\Console\Commands;

use App\Enums\AnswerState;
use App\Enums\FlashcardInteractiveAction as Action;
use App\Models\User;
use App\Services\Flashcard\Exceptions\FlashcardAlreadyExistsException;
use App\Services\Flashcard\FlashcardServiceInterface;
use App\Services\Flashcard\PracticeEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FlashcardInteractiveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashcard:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A spaced repetition tool for memorising questions and their respective answers.';

    protected FlashcardServiceInterface $flashcardService;

    /**
     * Execute the console command.
     *
     * @param FlashcardServiceInterface $flashcardService
     * @return int
     */
    public function handle(FlashcardServiceInterface $flashcardService): int
    {
        $this->flashcardService = $flashcardService;

        $user = User::findOrFail(1);

        do {
            $action = $this->chooseAction();

            match ($action) {
                Action::CREATE_FLASHCARD => $this->createFlashcard(),
                Action::LIST_FLASHCARDS => $this->listFlashcards(),
                Action::PRACTICE => $this->startPractice($user),
                Action::STATS => $this->displayStats(),
                Action::RESET => $this->resetProgress($user),
                default => $action
            };
        } while ($action !== Action::EXIT);

        return 0;
    }

    protected function chooseAction(): Action
    {
        $action = $this->choice('Select action', Action::actions());

        return Action::tryFrom($action);
    }

    protected function createFlashcard()
    {
        $question = $this->ask('Question');
        $answer = $this->ask('Answer');

        // try storing a new flashcard or fail with the duplicate error
        try {
            $this->flashcardService->createFlashcard($question, $answer);

            $this->info('Flashcard added successfully!');
        } catch (FlashcardAlreadyExistsException $e) {
            $this->error($e->getMessage());
        }
    }

    protected function listFlashcards()
    {
        // retrieve flashcards
        $flashcards = $this->flashcardService->listFlashcards();

        // display a table with questions and answers
        $this->info('Available flashcards');
        $this->table(
            ['Question', 'Answer'],
            $flashcards->map->only(['question', 'answer'])
        );
    }

    protected function startPractice(User $user)
    {
        while (true) {
            $this->separator();

            // retrieve flashcards with user answers
            $status = $this->flashcardService->getPracticeStatus($user);

            // display a table with questions and their answer status
            $this->info('Practice overview:');
            $this->table(
                ['Question', 'Status'],
                $status->getPracticeEntries()->map(fn(PracticeEntry $entry) => [
                    $entry->getQuestion(),
                    $entry->getAnswerState()->value
                ])
            );
            // display completion % as the tables footer
            $this->info("Completion progress: {$status->getCompletionProgress()}%");

            // ask the user to select a question or stop
            $entry = $this->chooseEntry($status->getPracticeEntries());

            // user selected `Stop`
            if ($entry === null) {
                return;
            }

            if ($entry->getAnswerState() === AnswerState::CORRECT) {
                // warn the user if the question is already correctly answered
                $this->warn('This question is already answered!');
            } else {
                // ask and store user's answer
                $answer = $this->ask($entry->getQuestion());
                $result = $this->flashcardService->acceptAnswer($entry->getFlashcard(), $user, $answer);

                match ($result) {
                    AnswerState::CORRECT => $this->info('The answer is correct!'),
                    AnswerState::INCORRECT => $this->warn('The answer is incorrect!'),
                    default => $this->error('This should not have happened: unsupported answer state!'),
                };
            }
        }
    }

    protected function displayStats()
    {
        // retrieve all flashcards
        $stats = $this->flashcardService->getStatistics();

        // display total number of questions, % of answered, % of correctly answered
        $this->table(
            ['Total number of questions', 'Answered', 'Correctly answered'],
            [
                [
                    $stats->getTotalQuestions(),
                    $stats->getAnsweredPercent() . '%',
                    $stats->getAnsweredCorrectlyPercent() . '%'
                ]
            ]
        );
    }

    protected function resetProgress(User $user)
    {
        $confirmed = $this->confirm('This action will reset all progress, do you wish to continue?');

        if (!$confirmed) {
            return;
        }

        $this->flashcardService->resetProgress($user);

        $this->info('Your progress has been reset and all answers deleted!');
    }

    /**
     * @param array|Collection<PracticeEntry> $entries
     * @return PracticeEntry|null
     */
    protected function chooseEntry(array|Collection $entries): ?PracticeEntry
    {
        $selection = $this->choice(
            'Select a question to answer or choose `Stop` to return',
            $entries
                ->map(fn(PracticeEntry $entry) => $entry->getQuestion())
                ->prepend('Stop')
                ->toArray()
        );

        if ($selection === 'Stop') {
            return null;
        }

        return $entries->first(fn(PracticeEntry $entry) => $entry->getQuestion() === $selection);
    }

    protected function separator()
    {
        $this->newLine();
        $this->line('────────────────────────');
        $this->newLine();
    }
}
