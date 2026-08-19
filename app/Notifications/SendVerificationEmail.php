<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class SendVerificationEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public string $code;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Email Verification Code')
            ->greeting('Hello ' . ($notifiable->firstname ?? 'Applicant') . ',')
            ->line('Thank you for registering. Please use the following 6-digit verification code to verify your email address:')
            ->line('**' . $this->code . '**')
            ->line('This code will expire in 30 minutes.')
            ->line('If you did not create an account, no further action is required.');
    }
}
