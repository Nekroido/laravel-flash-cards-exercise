<?php

namespace Tests\Unit\Services;

use App\Contracts\FlashcardRepositoryInterface;
use App\Contracts\UserAnswerRepositoryInterface;
use App\Enums\AnswerState;
use App\Models\Flashcard;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\Flashcard\Exceptions\CorrectAnswerAlreadyExistsException;
use App\Services\Flashcard\Exceptions\FlashcardAlreadyExistsException;
use App\Services\Flashcard\FlashcardService;
use App\Services\Flashcard\PracticeStatus;
use App\Services\Flashcard\Statistic;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class FlashcardServiceTest extends TestCase
{
    private FlashcardService $flashcardService;
    private MockObject|FlashcardRepositoryInterface $flashcardRepositoryMock;
    private MockObject|UserAnswerRepositoryInterface $answerRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flashcardRepositoryMock = $this->createMock(FlashcardRepositoryInterface::class);
        $this->answerRepositoryMock = $this->createMock(UserAnswerRepositoryInterface::class);

        $this->flashcardService = new FlashcardService($this->flashcardRepositoryMock, $this->answerRepositoryMock);
    }

    /** @test */
    public function it_can_list_flashcards(): void
    {
        $flashcards = Flashcard::factory(5)->make();

        $this->flashcardRepositoryMock
            ->expects($this->once())
            ->method('getAvailable')
            ->willReturn($flashcards);

        $result = $this->flashcardService->listFlashcards();

        $this->assertSameSize($flashcards, $result);
        $this->assertContainsOnlyInstancesOf(Flashcard::class, $result);
    }

    /** @test */
    public function it_can_create_a_flashcard(): void
    {
        $this->flashcardRepositoryMock
            ->expects($this->once())
            ->method('questionExists')
            ->with('Foo')
            ->willReturn(false);

        $this->flashcardRepositoryMock
            ->expects($this->once())
            ->method('storeFlashcard')
            ->with(
                $this->callback(
                    fn(Flashcard $flashcard) => $flashcard->question === 'Foo' && $flashcard->answer === 'Bar'
                )
            );

        $this->flashcardService->createFlashcard('Foo', 'Bar');
    }

    /** @test */
    public function it_fails_to_create_a_duplicated_flashcard(): void
    {
        $this->flashcardRepositoryMock
            ->method('questionExists')
            ->willReturn(true);

        $this->flashcardRepositoryMock
            ->expects($this->never())
            ->method('storeFlashcard');

        $this->expectException(FlashcardAlreadyExistsException::class);

        $this->flashcardService->createFlashcard('Foo', 'Bar');
    }

    /** @test */
    public function it_can_display_practice_status_for_user(): void
    {
        $flashcards = Flashcard::factory(5)->make();
        $user = User::factory()->makeOne(['id' => 1]);

        $answerFactory = UserAnswer::factory()->for($user);

        // 2 got correct answers
        $flashcards
            ->take(2)
            ->each(
                fn(Flashcard $flashcard) => $flashcard->answers->add(
                    $answerFactory->for($flashcard)->makeOne(['state' => AnswerState::CORRECT])
                )
            );

        // next 2 got incorrect answers
        $flashcards
            ->skip(2)
            ->take(2)
            ->each(
                fn(Flashcard $flashcard) => $flashcard->answers->add(
                    $answerFactory->for($flashcard)->makeOne(['state' => AnswerState::INCORRECT])
                )
            );

        $this->flashcardRepositoryMock
            ->expects($this->once())
            ->method('getFlashcardsWithUserAnswers')
            ->with($user->id)
            ->willReturn($flashcards);

        $result = $this->flashcardService->getPracticeStatus($user);

        $this->assertInstanceOf(PracticeStatus::class, $result);
        $this->assertCount(5, $result->getPracticeEntries());
        $this->assertEquals(40, $result->getCompletionProgress()); // 100 / 5 * 2
    }

    /**
     * @test
     * @dataProvider answerRegistrationDataProvider
     */
    public function it_can_register_new_answer_for_user_and_return_its_state(
        string $correctAnswer,
        string $answer,
        AnswerState $expectedState
    ): void {
        $flashcard = Flashcard::factory()->makeOne(['id' => fake()->numberBetween(), 'answer' => $correctAnswer]);
        $user = User::factory()->makeOne(['id' => fake()->numberBetween()]);

        $answerFactory = UserAnswer::factory()->for($flashcard)->for($user)->set('answer', $answer);
        if ($expectedState === AnswerState::CORRECT) {
            $answerFactory = $answerFactory->correct($flashcard);
        }

        $userAnswer = $answerFactory->makeOne();

        $this->answerRepositoryMock
            ->expects($this->once())
            ->method('getAnswer')
            ->with($flashcard->id, $user->id)
            ->willReturn(null);

        $this->answerRepositoryMock
            ->expects($this->once())
            ->method('storeAnswer')
            ->with(
                $this->callback(
                    fn(UserAnswer $a) => $a->answer === $answer
                        && $a->user === $user
                        && $a->flashcard === $flashcard
                        && $a->state === $expectedState
                )
            );

        $result = $this->flashcardService->acceptAnswer($flashcard, $user, $answer);

        $this->assertEquals($expectedState, $result);
    }

    /** @test */
    public function it_can_register_a_corrected_answer_for_user(): void
    {
        $flashcard = Flashcard::factory()->makeOne(['id' => fake()->numberBetween(), 'answer' => 'Bar']);
        $user = User::factory()->makeOne(['id' => fake()->numberBetween()]);

        $answerFactory = UserAnswer::factory()->for($flashcard)->for($user)->set('answer', 'Baz');
        $userAnswer = $answerFactory->makeOne();

        $this->answerRepositoryMock
            ->expects($this->once())
            ->method('getAnswer')
            ->with($flashcard->id, $user->id)
            ->willReturn($userAnswer);

        $result = $this->flashcardService->acceptAnswer($flashcard, $user, 'Bar');

        $this->assertEquals(AnswerState::CORRECT, $result);
    }

    /** @test */
    public function it_fails_to_register_an_answer_for_answered_flashcard(): void
    {
        $flashcard = Flashcard::factory()->makeOne(['id' => fake()->numberBetween(), 'answer' => 'Bar']);
        $user = User::factory()->makeOne(['id' => fake()->numberBetween()]);

        $userAnswer = UserAnswer::factory()->for($flashcard)->for($user)->correct($flashcard)->makeOne();

        $this->answerRepositoryMock
            ->expects($this->once())
            ->method('getAnswer')
            ->with($flashcard->id, $user->id)
            ->willReturn($userAnswer);

        $this->answerRepositoryMock
            ->expects($this->never())
            ->method('storeAnswer');

        $this->expectException(CorrectAnswerAlreadyExistsException::class);

        $this->flashcardService->acceptAnswer($flashcard, $user, 'Bar');
    }

    /** @test */
    public function it_can_reset_practice_progress_for_user(): void
    {
        $user = User::factory()->makeOne(['id' => fake()->numberBetween()]);

        $this->answerRepositoryMock
            ->expects($this->once())
            ->method('purgeAnswers')
            ->with($user->id);

        $this->flashcardService->resetProgress($user);
    }

    /** @test */
    public function it_can_display_flashcard_statistics(): void
    {
        $stats = [
            'total_questions' => 123,
            'total_answers' => 64,
            'correct_answers' => 55
        ];

        $this->flashcardRepositoryMock
            ->expects($this->once())
            ->method('getStatistics')
            ->willReturn($stats);

        $result = $this->flashcardService->getStatistics();

        $this->assertInstanceOf(Statistic::class, $result);
        $this->assertEquals(123, $result->getTotalQuestions());
        $this->assertEquals(52, $result->getAnsweredPercent());
        $this->assertEquals(45, $result->getAnsweredCorrectlyPercent());
    }

    public function answerRegistrationDataProvider(): array
    {
        return [
            ['Foo', 'Bar', AnswerState::INCORRECT],
            ['Test', 'Test', AnswerState::CORRECT],
        ];
    }
}
