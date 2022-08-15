<?php

namespace App\Contracts;

use App\Models\Flashcard;
use Illuminate\Support\Collection;

interface FlashcardRepositoryInterface
{
    /**
     * @return Collection<Flashcard>|Flashcard[]
     */
    public function getAvailable(): Collection|array;

    public function questionExists(string $question): bool;

    public function storeFlashcard(Flashcard $flashcard): void;

    /**
     * @param int $userId
     * @return Collection<Flashcard>|Flashcard[]
     */
    public function getFlashcardsWithUserAnswers(int $userId): Collection|array;

    /**
     * @return array{total_questions: int, total_answers: int, correct_answers: int}
     */
    public function getStatistics(): array;
}
