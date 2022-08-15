<?php

namespace App\Enums;

enum AnswerState: string
{
    case UNANSWERED = 'unanswered';
    case CORRECT = 'correct';
    case INCORRECT = 'incorrect';
}
