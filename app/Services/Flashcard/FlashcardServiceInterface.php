<?php

namespace App\Services\Flashcard;

use App\Enums\AnswerState;
use App\Models\Flashcard;
use App\Models\User;
use App\Services\Flashcard\Exceptions\CorrectAnswerAlreadyExistsException;
use App\Services\Flashcard\Exceptions\FlashcardAlreadyExistsException;
use Illuminate\Support\Collection;

interface FlashcardServiceInterface
{
    /**
     * Retrieves all available flashcards.
     *
     * @return Collection<Flashcard>|Flashcard[]
     */
    public function listFlashcards(): Collection|array;

    /**
     * Creates and stores a new flashcard.
     *
     * @param string $question
     * @param string $answer
     * @return void
     *
     * @throws FlashcardAlreadyExistsException
     */
    public function createFlashcard(string $question, string $answer): void;

    /**
     * Retrieves practice status for a user.
     *
     * @param User $user
     * @return PracticeStatus
     */
    public function getPracticeStatus(User $user): PracticeStatus;

    /**
     * Stores user's answer.
     *
     * @param Flashcard $flashcard
     * @param User $user
     * @param string $answer
     * @return AnswerState
     *
     * @throws CorrectAnswerAlreadyExistsException
     */
    public function acceptAnswer(Flashcard $flashcard, User $user, string $answer): AnswerState;

    /**
     * Purges all answers for a user.
     *
     * @param User $user
     * @return void
     */
    public function resetProgress(User $user): void;

    /**
     * Retrieves flashcard statistics.
     *
     * @return Statistic
     */
    public function getStatistics(): Statistic;
}
