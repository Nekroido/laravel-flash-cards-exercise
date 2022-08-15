<?php

namespace App\Services\Flashcard\Exceptions;

class CorrectAnswerAlreadyExists extends FlashcardException
{
    public function __construct()
    {
        parent::__construct("The correct answer already exists!");
    }
}
