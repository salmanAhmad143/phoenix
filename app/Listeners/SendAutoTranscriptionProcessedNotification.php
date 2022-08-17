<?php

namespace App\Listeners;

use App\Events\AutoTranscriptionGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\AutoTranscriptionCompletes;

class SendAutoTranscriptionProcessedNotification
{

    /**
     * Handle the event.
     *
     * @param  AutoTranscriptionGenerated  $event
     * @return void
     */
    public function handle(AutoTranscriptionGenerated $event)
    {
        Mail::to($event->notify['email'])->send(new AutoTranscriptionCompletes($event->notify));
    }
}
