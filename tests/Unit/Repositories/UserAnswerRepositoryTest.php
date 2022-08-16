<?php

namespace Tests\Unit\Repositories;

use App\Models\Flashcard;
use App\Models\User;
use App\Models\UserAnswer;
use App\Repositories\UserAnswerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAnswerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserAnswerRepository $answerRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->answerRepository = new UserAnswerRepository();
    }

    /** @test */
    public function it_stores_an_answer(): void
    {
        /** @var UserAnswer $answer */
        $answer = UserAnswer::factory()->makeOne();

        $this->answerRepository->storeAnswer($answer);

        $this->assertModelExists($answer);
    }

    /** @test */
    public function it_retrieves_an_existing_answer(): void
    {
        /** @var UserAnswer $answer */
        $answer = UserAnswer::factory()->createOne();

        $result = $this->answerRepository->getAnswer($answer->flashcard->id, $answer->user->id);

        $this->assertEquals($answer->toArray(), $result->toArray());
    }

    /** @test */
    public function it_returns_null_if_answer_doesnt_exist(): void
    {
        $result = $this->answerRepository->getAnswer(
            Flashcard::factory()->createOne()->id,
            User::factory()->createOne()->id,
        );

        $this->assertNull($result);
    }

    /** @test */
    public function it_purges_all_answers_for_the_user(): void
    {
        $user = User::factory()->createOne();
        UserAnswer::factory(3)->for($user)->create();

        $anotherUser = User::factory()->createOne();
        UserAnswer::factory(5)->for($anotherUser)->create();

        $this->answerRepository->purgeAnswers($user->id);

        $this->assertDatabaseMissing(UserAnswer::class, ['user_id' => $user->id]);
        $this->assertDatabaseHas(UserAnswer::class, ['user_id' => $anotherUser->id]);
    }
}
