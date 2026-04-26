<?php

namespace App\Services\Communication;

use App\Models\AcademicManagement\TeacherAssignment;
use App\Models\Communication\CommunicationGroup;
use App\Models\Communication\CommunicationGroupMember;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RecipientResolverService
{
    public function resolve(string $audienceType, array $context = []): Collection
    {
        $recipients = match ($audienceType) {
            'all' => $this->resolveAll(),
            'students' => $this->resolveStudents($context),
            'parents' => $this->resolveParents($context),
            'staff' => $this->resolveStaff($context),
            'teachers' => $this->resolveTeachers($context),
            'class' => $this->resolveClassAudience($context),
            'section' => $this->resolveSectionAudience($context),
            'individual' => $this->resolveIndividuals($context['recipients'] ?? []),
            default => throw ValidationException::withMessages([
                'audience_type' => 'Unsupported audience type.',
            ]),
        };

        return $this->normalizeRecipients($recipients);
    }

    public function resolveGroupMembers(CommunicationGroup $group): Collection
    {
        $group->loadMissing('members');

        if (in_array($group->group_type, ['class', 'section'], true)) {
            return $this->groupAudienceFromContext($group);
        }

        return $this->normalizeRecipients(
            $group->members->map(fn (CommunicationGroupMember $member) => [
                'recipient_type' => $member->member_type,
                'recipient_id' => (int) $member->member_id,
                'recipient' => $member->resolveMember(),
            ])
        );
    }

    protected function resolveAll(): Collection
    {
        return $this->normalizeRecipients(
            collect()
                ->concat($this->resolveStudents([]))
                ->concat($this->resolveParents([]))
                ->concat($this->resolveStaff([]))
        );
    }

    protected function resolveStudents(array $context): Collection
    {
        $query = Student::query();

        $this->applyEnrollmentScope($query, $context);

        return $query->get()->map(fn (Student $student) => [
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'recipient' => $student,
        ]);
    }

    protected function resolveParents(array $context): Collection
    {
        $students = Student::query();
        $this->applyEnrollmentScope($students, $context);

        return Guardian::query()
            ->whereHas('students', fn ($query) => $query->whereIn('students.id', $students->select('students.id')))
            ->get()
            ->map(fn (Guardian $guardian) => [
                'recipient_type' => 'guardian',
                'recipient_id' => $guardian->id,
                'recipient' => $guardian,
            ]);
    }

    protected function resolveStaff(array $context): Collection
    {
        return Staff::query()
            ->when($context['staff_ids'] ?? null, fn ($query, array $ids) => $query->whereIn('id', $ids))
            ->get()
            ->map(fn (Staff $staff) => [
                'recipient_type' => 'staff',
                'recipient_id' => $staff->id,
                'recipient' => $staff,
            ]);
    }

    protected function resolveTeachers(array $context): Collection
    {
        $assignments = TeacherAssignment::query()
            ->when($context['academic_year_id'] ?? null, fn ($query, $value) => $query->where('academic_year_id', $value))
            ->when($context['class_id'] ?? null, fn ($query, $value) => $query->where('school_class_id', $value))
            ->when($context['section_id'] ?? null, fn ($query, $value) => $query->where('section_id', $value))
            ->where('status', 'active')
            ->with('staff')
            ->get();

        return $assignments
            ->map(fn (TeacherAssignment $assignment) => $assignment->staff)
            ->filter()
            ->map(fn (Staff $staff) => [
                'recipient_type' => 'staff',
                'recipient_id' => $staff->id,
                'recipient' => $staff,
            ]);
    }

    protected function resolveClassAudience(array $context): Collection
    {
        if (! isset($context['class_id'])) {
            throw ValidationException::withMessages([
                'class_id' => 'class_id is required for class audience.',
            ]);
        }

        return $this->resolveStudents($context);
    }

    protected function resolveSectionAudience(array $context): Collection
    {
        if (! isset($context['class_id'], $context['section_id'])) {
            throw ValidationException::withMessages([
                'section_id' => 'class_id and section_id are required for section audience.',
            ]);
        }

        return $this->resolveStudents($context);
    }

    protected function resolveIndividuals(array $recipients): Collection
    {
        if ($recipients === []) {
            throw ValidationException::withMessages([
                'recipients' => 'Recipients are required for individual audience.',
            ]);
        }

        return collect($recipients)->map(function (array $recipient): array {
            $type = $recipient['recipient_type'] ?? null;
            $id = (int) ($recipient['recipient_id'] ?? 0);

            $model = match ($type) {
                'student' => Student::query()->find($id),
                'guardian' => Guardian::query()->find($id),
                'staff' => Staff::query()->find($id),
                'user' => User::query()->find($id),
                default => null,
            };

            if (! $model) {
                throw ValidationException::withMessages([
                    'recipients' => 'One or more selected recipients are invalid.',
                ]);
            }

            return [
                'recipient_type' => $type,
                'recipient_id' => $id,
                'recipient' => $model,
            ];
        });
    }

    protected function groupAudienceFromContext(CommunicationGroup $group): Collection
    {
        $context = [
            'class_id' => $group->class_id,
            'section_id' => $group->section_id,
        ];

        return match ($group->group_type) {
            'class' => $this->resolveClassAudience($context),
            'section' => $this->resolveSectionAudience($context),
            'staff' => $this->resolveStaff($context),
            default => collect(),
        };
    }

    protected function applyEnrollmentScope($query, array $context): void
    {
        $query->whereHas('enrollments', function ($enrollmentQuery) use ($context): void {
            $enrollmentQuery->where('is_current', true)
                ->when($context['academic_year_id'] ?? null, fn ($query, $value) => $query->where('academic_year_id', $value))
                ->when($context['class_id'] ?? null, fn ($query, $value) => $query->where('school_class_id', $value))
                ->when($context['section_id'] ?? null, fn ($query, $value) => $query->where('section_id', $value));
        });
    }

    protected function normalizeRecipients(Collection $recipients): Collection
    {
        return $recipients
            ->filter(fn ($recipient) => filled($recipient['recipient_type'] ?? null) && filled($recipient['recipient_id'] ?? null))
            ->unique(fn (array $recipient) => $recipient['recipient_type'].':'.$recipient['recipient_id'])
            ->values();
    }
}
