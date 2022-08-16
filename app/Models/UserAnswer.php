<?php

namespace App\Models;

use App\Enums\AnswerState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @property-read int $id
 * @property string $answer
 * @property AnswerState $state
 *
 * @property-read User|null $user
 * @property-read Flashcard|null $flashcard
 *
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * @method Builder|static forUserId(int $userId)
 * @method Builder|static correct()
 */
class UserAnswer extends Model
{
    use HasFactory;

    public $fillable = [
        'answer'
    ];

    public $casts = [
        'state' => AnswerState::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }

    public static function scopeForUserId(Builder $builder, int $userId): Builder
    {
        return $builder->where('user_id', $userId);
    }

    public static function scopeCorrect(Builder $builder): Builder
    {
        return $builder->where('state', AnswerState::CORRECT->value);
    }
}
