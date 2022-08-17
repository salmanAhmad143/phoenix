<?php

namespace App\Listeners;

use App\Events\AutoTranslationGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\AutoTranslationCompletes;

class SendAutoTranslationProcessedNotification
{
    /**
     * Handle the event.
     *
     * @param  AutoTranslationGenerated  $event
     * @return void
     */
    public function handle(AutoTranslationGenerated $event)
    {
        Mail::to($event->notify['email'])->send(new AutoTranslationCompletes($event->notify));
    }
}
