## Overview

This is an implementation of flashcard practice tool.

### Current limitations

- A user is hardcoded to ID=1;
- No data validation of questions and answers.

## Requirements

- PHP 8.1 installation with Composer;
- A running Docker instance.

## Setting up

Install the required Composer dependencies with the following command:

`composer install`

---
Start the application's instance with:

`./vendor/bin/sail up -d`

You can stop the application with:

`./vendor/bin/sail down`

---
Create all necessary tables:

`./vendor/bin/sail artisan migrate`

Create a test user:

`./vendor/bin/sail artisan db:seed`

## Using the CLI

Start the tool with the following command:

`./vendor/bin/sail artisan flashcard:interactive`

### Create a flashcard

Asks for a question and a correct answer and creates a new flashcard. There can be no flashcards with the same question.

### List all flashcards

Displays the table of all available flashcards with their questions and answers.

### Practice

Will list all available questions and their practice status for the current user:

- unanswered;
- correct;
- incorrect;
  A user can pick any question to answer or select `Stop` to quit to the main menu.

### Stats

Displays the total number of questions, and the amount of questions that got answered at least once and have at least
one correct answer.

### Reset

Will purge the practice progress for the current user.

### Exit

Will terminate the tool and return to shell.

## Testing

Run available tests with:
`./vendor/bin/sail test`
