<?php

namespace App\Enums;

enum AnswerState: string
{
    case CORRECT = 'correct';
    case INCORRECT = 'incorrect';
}
