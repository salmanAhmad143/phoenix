<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Events\MediaWasProcessed;
use App\Mail\MediaReady;

class SendMediaProcessedNotification
{

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MediaWasProcessed $event)
    {
        Mail::to($event->notify['email'])->send(new MediaReady($event->notify));
    }
}
