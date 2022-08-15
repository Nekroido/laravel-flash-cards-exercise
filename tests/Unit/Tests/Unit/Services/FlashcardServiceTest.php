<?php

namespace Tests\Unit\Services;

use App\Contracts\FlashcardRepositoryInterface;
use App\Contracts\UserAnswerRepositoryInterface;
use App\Services\Flashcard\FlashcardService;
use PHPUnit\Framework\TestCase;

class FlashcardServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->flashcardRepositoryMock = $this->createMock(FlashcardRepositoryInterface::class);
        $this->answerRepositoryMock = $this->createMock(UserAnswerRepositoryInterface::class);

        $this->flashcardService = new FlashcardService($this->flashcardRepositoryMock, $this->answerRepositoryMock);
    }

    public function it_can_create_a_flashcard(): void
    {
    }

    public function it_fails_to_create_a_duplicated_flashcard(): void
    {
    }

    public function it_can_list_flashcards(): void
    {
    }

    public function it_can_list_questions_with_status_for_user(): void
    {
    }

    public function it_can_display_practice_progress_for_user(): void
    {
    }

    public function it_can_register_new_answer_for_user(): void
    {
    }

    public function it_fails_to_register_an_answer_for_answered_flashcard(): void
    {
    }

    public function it_can_display_flashcard_statistics(): void
    {
    }

    public function it_can_reset_practice_progress_for_user(): void
    {
    }
}
