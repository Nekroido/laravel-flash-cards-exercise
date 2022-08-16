<?php

namespace Database\Factories;

use App\Models\Flashcard;
use App\Models\User;
use App\Models\UserAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAnswer>
 */
class UserAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'answer' => fake()->text,
            'user_id' => User::factory(),
            'flashcard_id' => Flashcard::factory(),
        ];
    }

    public function correct(Flashcard $flashcard): static
    {
        return $this->state(fn($_) => [
            'flashcard_id' => $flashcard,
            'answer' => $flashcard->answer
        ]);
    }
}
