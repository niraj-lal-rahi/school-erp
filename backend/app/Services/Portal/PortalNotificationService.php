<?php

namespace App\Services\Portal;

use App\Models\Communication\Announcement;
use App\Models\Communication\CommunicationMessage;
use App\Models\Portal\PortalNotification;
use App\Models\Student;
use App\Models\User;
use App\Repositories\Contracts\Portal\PortalAccessRepositoryInterface;
use App\Repositories\Contracts\Portal\PortalNotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PortalNotificationService
{
    public function __construct(
        protected PortalNotificationRepositoryInterface $notifications,
        protected PortalAccessRepositoryInterface $accesses,
        protected PortalActivityLogService $activityLogs,
    ) {
    }

    public function list(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $this->syncForUser($user, $filters['student_id'] ?? null);

        return $this->notifications->paginateForUser($user->id, $filters, $perPage);
    }

    public function markRead(User $user, PortalNotification $notification): PortalNotification
    {
        if ((int) $notification->user_id !== (int) $user->id) {
            abort(403, 'This notification does not belong to the current user.');
        }

        $updated = $this->notifications->update($notification, [
            'is_read' => true,
            'read_at' => now(),
        ]);

        $this->activityLogs->log($user, 'portal.notification_read', [
            'student_id' => $updated->student_id,
            'description' => 'Portal notification marked as read.',
            'metadata' => ['notification_id' => $updated->id],
        ]);

        return $updated;
    }

    public function markAllRead(User $user): int
    {
        $rows = PortalNotification::query()
            ->where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        $this->activityLogs->log($user, 'portal.notifications_read_all', [
            'description' => 'All portal notifications marked as read.',
            'metadata' => ['count' => $rows],
        ]);

        return $rows;
    }

    public function syncForUser(User $user, ?int $studentId = null): int
    {
        $count = 0;
        $accessibleStudents = $this->accesses->getAccessibleStudents($user->id);

        foreach ($accessibleStudents as $student) {
            if ($studentId && (int) $student->id !== (int) $studentId) {
                continue;
            }

            $count += $this->syncAnnouncements($user, $student);
            $count += $this->syncMessages($user, $student);
        }

        return $count;
    }

    protected function syncAnnouncements(User $user, Student $student): int
    {
        $student->loadMissing(['enrollments' => fn ($query) => $query->latest('id')]);
        $enrollment = $student->enrollments->firstWhere('is_current', true);
        $guardianIds = $this->accesses->getActiveAccessesByUser($user->id)
            ->where('student_id', $student->id)
            ->pluck('guardian_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $announcements = Announcement::query()
            ->where('status', 'published')
            ->when($student->id, function (Builder $query) use ($student, $enrollment): void {
                $query->where(function (Builder $inner) use ($student, $enrollment, $user, $guardianIds): void {
                    $inner->whereIn('audience_type', ['all', 'students', 'parents'])
                        ->orWhereHas('recipients', fn (Builder $recipientQuery) => $recipientQuery
                            ->where(function (Builder $recipientTypes) use ($student, $user, $guardianIds): void {
                                $recipientTypes->where(function (Builder $studentRecipient) use ($student): void {
                                    $studentRecipient->where('recipient_type', 'student')
                                        ->where('recipient_id', $student->id);
                                })->orWhere(function (Builder $userRecipient) use ($user): void {
                                    $userRecipient->where('recipient_type', 'user')
                                        ->where('recipient_id', $user->id);
                                });

                                if ($guardianIds !== []) {
                                    $recipientTypes->orWhere(function (Builder $guardianRecipient) use ($guardianIds): void {
                                        $guardianRecipient->where('recipient_type', 'guardian')
                                            ->whereIn('recipient_id', $guardianIds);
                                    });
                                }
                            }))
                        ->orWhere(function (Builder $classQuery) use ($enrollment): void {
                            if (! $enrollment) {
                                $classQuery->whereRaw('1 = 0');

                                return;
                            }

                            $classQuery->where('audience_type', 'class')
                                ->where('class_id', $enrollment->school_class_id);
                        })
                        ->orWhere(function (Builder $sectionQuery) use ($enrollment): void {
                            if (! $enrollment || ! $enrollment->section_id) {
                                $sectionQuery->whereRaw('1 = 0');

                                return;
                            }

                            $sectionQuery->where('audience_type', 'section')
                                ->where('class_id', $enrollment->school_class_id)
                                ->where('section_id', $enrollment->section_id);
                        });
                });
            })
            ->limit(20)
            ->get();

        $created = 0;

        foreach ($announcements as $announcement) {
            $exists = PortalNotification::query()
                ->where('user_id', $user->id)
                ->where('student_id', $student->id)
                ->where('notification_type', 'announcement')
                ->where('title', $announcement->title)
                ->exists();

            if ($exists) {
                continue;
            }

            $this->notifications->create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'student_id' => $student->id,
                'title' => $announcement->title,
                'message' => $announcement->content,
                'notification_type' => 'announcement',
                'is_read' => false,
                'read_at' => null,
            ]);

            $created++;
        }

        return $created;
    }

    protected function syncMessages(User $user, Student $student): int
    {
        $messages = CommunicationMessage::query()
            ->whereIn('status', ['sent', 'delivered', 'read'])
            ->where(function (Builder $query) use ($user, $student): void {
                $query->where(function (Builder $messageQuery) use ($student): void {
                    $messageQuery->where('recipient_type', 'student')
                        ->where('recipient_id', $student->id);
                })->orWhere(function (Builder $messageQuery) use ($user): void {
                    $messageQuery->where('recipient_type', 'user')
                        ->where('recipient_id', $user->id);
                });
            })
            ->latest('sent_at')
            ->limit(20)
            ->get();

        $created = 0;

        foreach ($messages as $message) {
            $title = $message->subject ?: 'New message';
            $body = $message->body;

            $exists = PortalNotification::query()
                ->where('user_id', $user->id)
                ->where('student_id', $student->id)
                ->where('notification_type', 'message')
                ->where('title', $title)
                ->where('message', $body)
                ->exists();

            if ($exists) {
                continue;
            }

            $this->notifications->create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'student_id' => $student->id,
                'title' => $title,
                'message' => $body,
                'notification_type' => 'message',
                'is_read' => $message->read_at !== null,
                'read_at' => $message->read_at,
            ]);

            $created++;
        }

        return $created;
    }
}
