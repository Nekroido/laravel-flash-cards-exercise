<?php

namespace App\Contracts;

use App\Models\Flashcard;
use Illuminate\Support\Collection;

interface FlashcardRepositoryInterface
{
    /**
     * Retrieves a collection of available flashcards.
     *
     * @return Collection<Flashcard>|Flashcard[]
     */
    public function getAvailable(): Collection|array;

    /**
     * Checks if a flashcard exists with the question.
     *
     * @param string $question
     * @return bool
     */
    public function questionExists(string $question): bool;

    /**
     * Stores a flashcard.
     *
     * @param Flashcard $flashcard
     * @return void
     */
    public function storeFlashcard(Flashcard $flashcard): void;

    /**
     * Retrieves the list of available flashcards with answers of the given user.
     *
     * @param int $userId
     * @return Collection<Flashcard>|Flashcard[]
     */
    public function getFlashcardsWithUserAnswers(int $userId): Collection|array;

    /**
     * Aggregates flashcards statistics:
     * - total number of questions;
     * - questions with an answer;
     * - questions with a correct answer.
     *
     * @return array{total_questions: int, total_answers: int, correct_answers: int}
     */
    public function getStatistics(): array;
}
