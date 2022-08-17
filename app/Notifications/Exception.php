<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Exception extends Notification implements ShouldQueue
{
    use Queueable;

    protected $param;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Exception in ' . env('APP_NAME')) // it will use this class name if you don't specify
            ->greeting($this->param['url']) // example: Dear Sir, Hello Madam, etc ...
            ->level('error') // It is kind of email. Available options: info, success, error. Default: info
            ->line($this->param['location'] ?? "")
            ->line($this->param['file'] ?? "")
            ->line($this->param['line'] ?? "")
            // ->action('Notification Action', url('/'))
            ->line($this->param['msg']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
