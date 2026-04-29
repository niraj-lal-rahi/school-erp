<?php

namespace App\Listeners\Examination;

use App\Events\Examination\ResultsPublished;
use App\Services\Communication\NotificationService;
use Illuminate\Support\Collection;

class SendResultNotifications
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function handle(ResultsPublished $event): void
    {
        $publication = $event->publication->loadMissing(['exam.studentResults.student.guardians']);

        if (! $publication->notify_users || ! $publication->exam) {
            return;
        }

        $recipients = $this->buildRecipients($publication->exam->studentResults);

        if ($recipients->isEmpty()) {
            return;
        }

        $options = $event->options;

        $this->notifications->sendToRecipients($recipients, [
            'school_id' => $publication->school_id,
            'subject' => $options['subject'] ?? sprintf('%s results published', $publication->exam->name),
            'message' => $options['message'] ?? sprintf('Results for %s have been published. You can now review the report card and overall performance summary.', $publication->exam->name),
            'channel' => $options['channel'] ?? 'multi',
            'channels' => $options['channels'] ?? ['in_app', 'email'],
            'provider' => $options['provider'] ?? null,
        ]);
    }

    protected function buildRecipients(Collection $studentResults): Collection
    {
        return $studentResults
            ->flatMap(function ($studentResult): array {
                if (! $studentResult->student) {
                    return [];
                }

                $student = $studentResult->student;
                $recipients = [[
                    'recipient_type' => 'student',
                    'recipient_id' => (int) $student->id,
                    'recipient' => $student,
                ]];

                foreach ($student->guardians as $guardian) {
                    $recipients[] = [
                        'recipient_type' => 'guardian',
                        'recipient_id' => (int) $guardian->id,
                        'recipient' => $guardian,
                    ];
                }

                return $recipients;
            })
            ->unique(fn (array $recipient): string => $recipient['recipient_type'].'-'.$recipient['recipient_id'])
            ->values();
    }
}
