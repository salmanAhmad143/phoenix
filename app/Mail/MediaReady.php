<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MediaReady extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $notify = array();

    public function __construct(array $notify)
    {
        $this->notify = $notify;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Media Ready - ' . $this->notify['mediaName'])
                    ->view('emails.mediaReady')->with([
            'name' => $this->notify['name'],
            'mediaName' => $this->notify['mediaName']
        ]);
    }
}
