<?php

namespace App\Services\Flashcard;

class Statistic
{
    public function __construct(
        private readonly int $totalQuestions,
        private readonly int $answered,
        private readonly int $answeredCorrectly
    ) {
    }

    public function getTotalQuestions(): int
    {
        return $this->totalQuestions;
    }

    public function getAnsweredPercent(): int
    {
        return round($this->answered / $this->getTotalQuestions() * 100);
    }

    public function getAnsweredCorrectlyPercent(): int
    {
        return round($this->answeredCorrectly / $this->getTotalQuestions() * 100);
    }
}
