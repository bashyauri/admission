<?php

namespace Tests\Feature;

use App\Jobs\SendResultWorkflowNotificationEmail;
use App\Models\ResultWorkflowNotification;
use App\Models\User;
use App\Services\ResultWorkflowNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ResultWorkflowNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_persisted_for_recipient_and_email_is_queued_idempotently(): void
    {
        Queue::fake();
        $recipient = User::factory()->create();
        $service = app(ResultWorkflowNotificationService::class);

        $payload = [
            'course' => 'BIO101 Introduction to Biology',
            'session' => '2025/2026',
            'semester' => 'first',
            'action' => 'Review and approve or return the submitted results.',
        ];

        $service->notify(
            [$recipient],
            'lecturer_submission',
            'test-submission-event',
            'Results ready for Coordinator review',
            'A lecturer submitted course results for your review.',
            'https://example.test/coordinator/result-review',
            $payload
        );

        $service->notify(
            [$recipient],
            'lecturer_submission',
            'test-submission-event',
            'Results ready for Coordinator review',
            'A lecturer submitted course results for your review.',
            'https://example.test/coordinator/result-review',
            $payload
        );

        $this->assertSame(1, ResultWorkflowNotification::query()
            ->where('user_id', $recipient->id)
            ->where('event_key', 'test-submission-event:'.$recipient->id)
            ->count());

        Queue::assertPushed(SendResultWorkflowNotificationEmail::class, 1);
        Queue::assertPushedOn('notifications', SendResultWorkflowNotificationEmail::class);
        $this->assertDatabaseMissing('result_workflow_notifications', [
            'event_key' => 'test-submission-event:someone-else',
        ]);
    }
}
