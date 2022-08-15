<?php

namespace App\Contracts;

use App\Models\UserAnswer;

interface UserAnswerRepositoryInterface
{
    public function storeAnswer(UserAnswer $answer): void;

    public function getAnswer(int $flashcardId, int $userId): ?UserAnswer;

    public function purgeAnswers(int $userId): void;
}
