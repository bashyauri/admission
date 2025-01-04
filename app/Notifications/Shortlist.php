<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class Shortlist extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = "https://dhs.wufpbk.edu.ng/";


        return (new MailMessage)
            ->from('info@wufpbk.edu.ng', 'WUFPBK')
            ->subject('Notification of Admission.')
            ->markdown('emails.shortlist', [
                'candidateName' => $notifiable->full_name,
                'url' => $url,
                'programme_name' => $notifiable->programme->name,
                'department_name' => $notifiable->proposedCourse->department->name,

            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
