<?php

namespace App\Repositories;

use App\Contracts\FlashcardRepositoryInterface;
use App\Enums\AnswerState;
use App\Models\Flashcard;
use App\Models\UserAnswer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlashcardRepository implements FlashcardRepositoryInterface
{
    protected function getQueryBuilder(): Builder
    {
        return Flashcard::query();
    }

    /**
     * @inheritDoc
     */
    public function getAvailable(): Collection|array
    {
        return $this->getQueryBuilder()->get();
    }

    public function questionExists(string $question): bool
    {
        return $this->getQueryBuilder()->where('question', '=', $question)->exists();
    }

    // todo: consider adding exception
    public function storeFlashcard(Flashcard $flashcard): void
    {
        $flashcard->saveOrFail();
    }

    /**
     * @inheritDoc
     */
    public function getFlashcardsWithUserAnswers(int $userId): Collection|array
    {
        return $this->getQueryBuilder()
            ->with([
                'answers' => fn(HasMany|UserAnswer $b) => $b->forUserId($userId)
            ])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getStatistics(): array
    {
        return $this->getQueryBuilder()
            ->selectRaw('count(flashcards.id) as `total_questions`')
            ->selectRaw('count(a.id) as `total_answers`')
            ->selectRaw('sum(if(a.state = ?, 1, 0)) as `correct_answers`', [AnswerState::CORRECT->value])
            ->leftJoin('user_answers as a', 'a.flashcard_id', 'flashcards.id')
            ->first()
            ->toArray();
    }
}
