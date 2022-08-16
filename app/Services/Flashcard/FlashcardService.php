<?php

namespace App\Services\Flashcard;

use App\Contracts\FlashcardRepositoryInterface;
use App\Contracts\UserAnswerRepositoryInterface;
use App\Enums\AnswerState;
use App\Models\Flashcard;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\Flashcard\Exceptions\CorrectAnswerAlreadyExistsException;
use App\Services\Flashcard\Exceptions\FlashcardAlreadyExistsException;
use Illuminate\Support\Collection;

class FlashcardService implements FlashcardServiceInterface
{
    public function __construct(
        protected readonly FlashcardRepositoryInterface $flashcardRepository,
        protected readonly UserAnswerRepositoryInterface $answerRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function listFlashcards(): Collection|array
    {
        return $this->flashcardRepository->getAvailable();
    }

    /**
     * @inheritDoc
     */
    public function createFlashcard(string $question, string $answer): void
    {
        if ($this->flashcardRepository->questionExists($question)) {
            throw new FlashcardAlreadyExistsException($question);
        }

        $flashcard = Flashcard::make(['question' => $question, 'answer' => $answer]);

        $this->flashcardRepository->storeFlashcard($flashcard);
    }

    public function getPracticeStatus(User $user): PracticeStatus
    {
        $flashcards = $this->flashcardRepository->getFlashcardsWithUserAnswers($user->id);

        return new PracticeStatus($flashcards);
    }

    public function acceptAnswer(Flashcard $flashcard, User $user, string $answer): AnswerState
    {
        $userAnswer = $this->answerRepository->getAnswer($flashcard->id, $user->id);

        if ($userAnswer?->state === AnswerState::CORRECT) {
            throw new CorrectAnswerAlreadyExistsException();
        }

        $userAnswer = $userAnswer ?? UserAnswer::make();
        $userAnswer->answer = $answer;
        $userAnswer->user()->associate($user);
        $userAnswer->flashcard()->associate($flashcard);
        $userAnswer->state = $flashcard->answer === $answer ? AnswerState::CORRECT : AnswerState::INCORRECT;

        $this->answerRepository->storeAnswer($userAnswer);

        return $userAnswer->state;
    }

    public function resetProgress(User $user): void
    {
        $this->answerRepository->purgeAnswers($user->id);
    }

    public function getStatistics(): Statistic
    {
        $stats = $this->flashcardRepository->getStatistics();

        return new Statistic($stats['total_questions'], $stats['total_answers'], $stats['correct_answers']);
    }
}
