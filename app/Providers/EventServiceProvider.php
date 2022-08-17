<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\MediaWasProcessed;
use App\Listeners\SendMediaProcessedNotification;
use App\Events\AutoTranscriptionGenerated;
use App\Listeners\SendAutoTranscriptionProcessedNotification;
use App\Events\AutoTranslationGenerated;
use App\Listeners\SendAutoTranslationProcessedNotification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MediaWasProcessed::class => [
            SendMediaProcessedNotification::class,
        ],
        AutoTranscriptionGenerated::class => [
            SendAutoTranscriptionProcessedNotification::class,
        ],
        AutoTranslationGenerated::class => [
            SendAutoTranslationProcessedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
