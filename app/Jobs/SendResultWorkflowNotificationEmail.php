<?php

namespace App\Jobs;

use App\Models\ResultWorkflowNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendResultWorkflowNotificationEmail implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public string $notificationId)
    {
    }

    public function uniqueId(): string
    {
        return $this->notificationId;
    }

    public function handle(): void
    {
        $notification = ResultWorkflowNotification::with('user')->find($this->notificationId);
        if (!$notification || $notification->email_status === 'sent') {
            return;
        }

        if (!$notification->user?->email) {
            $notification->update([
                'email_status' => 'failed',
                'email_attempts' => $notification->email_attempts + 1,
                'email_failure' => 'Recipient has no email address.',
            ]);
            return;
        }

        $notification->update([
            'email_status' => 'sending',
            'email_attempts' => $notification->email_attempts + 1,
            'email_failure' => null,
        ]);

        try {
            $context = $notification->context ?? [];
            Mail::raw(
                collect([
                    $notification->message,
                    isset($context['course']) ? 'Course: '.$context['course'] : null,
                    isset($context['session']) ? 'Session: '.$context['session'] : null,
                    isset($context['semester']) ? 'Semester: '.$context['semester'] : null,
                    isset($context['action']) ? 'Required action: '.$context['action'] : null,
                    isset($context['reason']) ? 'Return reason: '.$context['reason'] : null,
                    $notification->action_url ? 'Open the staff portal: '.$notification->action_url : null,
                ])->filter()->implode("\n"),
                function ($message) use ($notification) {
                    $message->to($notification->user->email)->subject($notification->title);
                }
            );

            $notification->update([
                'email_status' => 'sent',
                'email_sent_at' => now(),
                'email_failure' => null,
            ]);
        } catch (Throwable $exception) {
            $isFinalAttempt = $this->attempts() >= $this->tries;
            $notification->update([
                'email_status' => $isFinalAttempt ? 'failed' : 'queued',
                'email_failure' => mb_substr($exception->getMessage(), 0, 4000),
            ]);
            report($exception);

            if (!$isFinalAttempt) {
                throw $exception;
            }
        }
    }
}
