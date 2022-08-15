<?php

namespace App\Repositories;

use App\Contracts\UserAnswerRepositoryInterface;
use App\Models\UserAnswer;

class UserAnswerRepository implements UserAnswerRepositoryInterface
{
    public function storeAnswer(UserAnswer $answer): void
    {
        $answer->saveOrFail();
    }

    public function getAnswer(int $flashcardId, int $userId): ?UserAnswer
    {
        /** @var UserAnswer|null */
        return UserAnswer::query()
            ->with(['flashcard', 'user'])
            ->whereRelation('flashcard', 'id', '=', $flashcardId)
            ->whereRelation('user', 'id', '=', $userId)
            ->first();
    }

    public function purgeAnswers(int $userId): void
    {
        UserAnswer::forUserId($userId)->delete();
    }
}
