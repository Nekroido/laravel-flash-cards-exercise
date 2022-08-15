<?php

namespace App\Services\Flashcard;

use App\Enums\AnswerState;
use App\Models\Flashcard;

final class PracticeEntry
{
    public function __construct(private readonly Flashcard $flashcard)
    {
    }

    public function getFlashcard(): Flashcard
    {
        return $this->flashcard;
    }

    public function getQuestion(): string
    {
        return $this->getFlashcard()->question;
    }

    public function getAnswerState(): AnswerState
    {
        return $this->getFlashcard()->answers->first()->state ?? AnswerState::UNANSWERED;
    }
}
