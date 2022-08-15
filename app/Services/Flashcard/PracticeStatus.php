<?php

namespace App\Services\Flashcard;

use App\Enums\AnswerState;
use App\Models\Flashcard;
use Illuminate\Support\Collection;

final class PracticeStatus
{
    /**
     * @var Collection<PracticeEntry>|array
     */
    private readonly Collection|array $entries;

    /**
     * @param Collection|Flashcard[] $flashcards
     */
    public function __construct(Collection|array $flashcards)
    {
        $this->entries = $flashcards->mapInto(PracticeEntry::class);
    }

    /**
     * @return Collection<PracticeEntry>|array
     */
    public function getPracticeEntries(): Collection|array
    {
        return $this->entries;
    }

    public function getCompletionProgress(): int
    {
        $correct = $this
            ->getPracticeEntries()
            ->where(fn(PracticeEntry $entry) => $entry->getAnswerState() === AnswerState::CORRECT)->count();

        return round(100 / $this->getPracticeEntries()->count() * $correct);
    }
}
