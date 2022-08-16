<?php

namespace App\Services\Flashcard\Exceptions;

class FlashcardAlreadyExistsException extends FlashcardException
{
    public function __construct(string $question)
    {
        parent::__construct("Flashcard for '$question' already exists!");
    }
}
