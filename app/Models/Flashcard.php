<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @mixin Builder
 *
 * @property-read int $id
 * @property string $question
 * @property string $answer
 *
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * @property-read Collection<UserAnswer>|UserAnswer[] $answers
 */
class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer'
    ];

    /**
     * @return HasMany<UserAnswer>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(UserAnswer::class);
    }
}
