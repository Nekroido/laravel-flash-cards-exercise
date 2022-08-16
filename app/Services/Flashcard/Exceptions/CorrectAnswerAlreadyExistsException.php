<?php

namespace App\Services\Flashcard\Exceptions;

class CorrectAnswerAlreadyExistsException extends FlashcardException
{
    public function __construct()
    {
        parent::__construct("The correct answer already exists!");
    }
}
