<?php

namespace App\Enums;

enum FlashcardInteractiveAction: string
{
    case CREATE_FLASHCARD = 'Create a flashcard';
    case LIST_FLASHCARDS = 'List all flashcard';
    case PRACTICE = 'Practice';
    case STATS = 'Stats';
    case RESET = 'Reset';
    case EXIT = 'Exit';

    public static function actions(): array
    {
        return array_map(fn(self $action) => $action->value, self::cases());
    }
}
