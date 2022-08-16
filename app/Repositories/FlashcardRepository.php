<?php

namespace App\Repositories;

use App\Contracts\FlashcardRepositoryInterface;
use App\Enums\AnswerState;
use App\Models\Flashcard;
use App\Models\UserAnswer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
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
            ->selectRaw('count(distinct flashcards.id) as `total_questions`')
            ->selectRaw('count(distinct any.flashcard_id) as `total_answers`')
            ->selectRaw('count(distinct correct.flashcard_id) as `correct_answers`')
            ->leftJoin('user_answers as any', 'any.flashcard_id', 'flashcards.id')
            ->join(
                'user_answers as correct',
                fn(JoinClause $join) => $join
                    ->on('correct.flashcard_id', '=', 'flashcards.id')
                    ->where('correct.state', '=', AnswerState::CORRECT->value),
                type: 'left'
            )
            ->first()
            ->toArray();
    }
}
