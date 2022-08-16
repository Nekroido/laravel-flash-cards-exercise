<?php

namespace Tests\Unit\Repositories;

use App\Models\Flashcard;
use App\Models\User;
use App\Models\UserAnswer;
use App\Repositories\FlashcardRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashcardRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FlashcardRepository $flashcardRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashcardRepository = new FlashcardRepository();
    }

    /** @test */
    public function it_lists_available_flashcards(): void
    {
        $flashcards = Flashcard::factory(15)->create();

        $result = $this->flashcardRepository->getAvailable();

        $this->assertSameSize($flashcards, $result);
        $this->assertContainsOnlyInstancesOf(Flashcard::class, $result);
    }

    /** @test */
    public function it_checks_if_a_question_already_exists(): void
    {
        $flashcardStored = Flashcard::factory()->createOne();
        $flashcardNew = Flashcard::factory()->makeOne();

        $result = $this->flashcardRepository->questionExists($flashcardStored->question);
        $this->assertTrue($result);

        $result = $this->flashcardRepository->questionExists($flashcardNew->question);
        $this->assertFalse($result);
    }

    /** @test */
    public function it_stores_a_flashcard(): void
    {
        $flashcard = Flashcard::factory()->makeOne();

        $this->flashcardRepository->storeFlashcard($flashcard);

        $this->assertModelExists($flashcard);
    }

    /** @test */
    public function it_lists_available_flashcards_with_answers_for_the_given_user(): void
    {
        $user = User::factory()->create();
        $flashcards = Flashcard::factory(15)->create();
        $flashcards->each(fn(Flashcard $flashcard) => $this->createUserAnswer($flashcard, $user));

        $validatorFn = function (Flashcard $flashcard) use ($user) {
            $this->assertCount(1, $flashcard->answers);
            $this->assertEquals($user->id, $flashcard->answers->first()->user->id);
        };

        $result = $this->flashcardRepository->getFlashcardsWithUserAnswers($user->id);

        $this->assertSameSize($flashcards, $result);
        $result->each($validatorFn);
    }

    /** @test */
    public function it_aggregates_statistics(): void
    {
        $flashcards = Flashcard::factory(10)->create();

        // 5 got correct answers
        $flashcards
            ->take(5)
            ->each(
                fn(Flashcard $flashcard) => $this->createUserAnswer(
                    $flashcard,
                    User::factory()->create(),
                    isCorrect: true
                )
            );
        // 2 got incorrect answers
        $flashcards
            ->skip(4)
            ->take(2)
            ->each(
                fn(Flashcard $flashcard) => $this->createUserAnswer(
                    $flashcard,
                    User::factory()->create(),
                    isCorrect: false
                )
            );

        $this->assertDatabaseCount(Flashcard::class, 10);
        $this->assertDatabaseCount(UserAnswer::class, 7);

        $result = $this->flashcardRepository->getStatistics();
        $this->assertArrayHasKey('total_questions', $result);
        $this->assertArrayHasKey('total_answers', $result);
        $this->assertArrayHasKey('correct_answers', $result);

        $this->assertEquals(
            [
                'total_questions' => 10,
                'total_answers' => 6,
                'correct_answers' => 5
            ],
            $result
        );
    }

    private function createUserAnswer(Flashcard $flashcard, User $user, bool $isCorrect = true): UserAnswer
    {
        $factory = UserAnswer::factory()
            ->for($user)
            ->for($flashcard);

        $factory = match ($isCorrect) {
            true => $factory->correct($flashcard), // correct answer
            false => $factory, // incorrect answer
        };

        return $factory->createOne();
    }
}
