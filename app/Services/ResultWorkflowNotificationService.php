<?php

namespace App\Services;

use App\Jobs\SendResultWorkflowNotificationEmail;
use App\Models\ResultWorkflowNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ResultWorkflowNotificationService
{
    /**
     * Persist the in-portal handoff first, then queue its email prompt.
     * Callers invoke this only after the workflow transition succeeds.
     */
    public function notify(
        iterable $recipients,
        string $eventType,
        string $eventKey,
        string $title,
        string $message,
        ?string $actionUrl,
        array $context = [],
    ): void {
        foreach ($recipients as $recipient) {
            if (!$recipient instanceof User) {
                continue;
            }

            $row = null;
            try {
                $row = ResultWorkflowNotification::firstOrCreate(
                    ['event_key' => $eventKey.':'.$recipient->id],
                    [
                        'id' => (string) Str::uuid(),
                        'user_id' => $recipient->id,
                        'event_type' => $eventType,
                        'title' => $title,
                        'message' => $message,
                        'action_url' => $actionUrl,
                        'context' => $context,
                    ]
                );

                if ($row->wasRecentlyCreated) {
                    SendResultWorkflowNotificationEmail::dispatch($row->id)
                        ->onConnection('database')
                        ->onQueue('notifications');
                }
            } catch (Throwable $exception) {
                try {
                    if ($row?->wasRecentlyCreated) {
                        $row->update([
                            'email_status' => 'failed',
                            'email_failure' => 'The email could not be queued. See application logs for details.',
                        ]);
                    }
                } catch (Throwable $loggingException) {
                    report($loggingException);
                }
                // Notification infrastructure must never undo a valid result transition.
                report($exception);
                Log::error('Unable to create result workflow notification.', [
                    'event_type' => $eventType,
                    'recipient_id' => $recipient->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }
}
