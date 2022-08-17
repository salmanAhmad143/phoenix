<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MediaAssignment extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $notify = null;
    public function __construct($notify)
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
        return $this->subject('Media assigned for ' . $this->notify['workFlowState'] . ' - ' . $this->notify['mediaName'])
            ->view('emails.mediaAssignment')->with([
                'name' => $this->notify['name'],
                'mediaName' => $this->notify['mediaName'],
                'workFlowState' => $this->notify['workFlowState']
            ]);
    }
}
