<?php

namespace App\Providers;

use App\Contracts\FlashcardRepositoryInterface;
use App\Contracts\UserAnswerRepositoryInterface;
use App\Repositories\FlashcardRepository;
use App\Repositories\UserAnswerRepository;
use App\Services\Flashcard\FlashcardService;
use App\Services\Flashcard\FlashcardServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(FlashcardRepositoryInterface::class, FlashcardRepository::class);
        $this->app->bind(UserAnswerRepositoryInterface::class, UserAnswerRepository::class);
        $this->app->bind(FlashcardServiceInterface::class, FlashcardService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
