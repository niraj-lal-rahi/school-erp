<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\AdmissionData;
use App\DataTransferObjects\SIS\GuardianData;
use App\DataTransferObjects\SIS\StudentData;
use App\Models\Admission;
use App\Models\Guardian;
use App\Repositories\Contracts\AdmissionRepositoryInterface;
use App\Repositories\Contracts\GuardianRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdmissionService
{
    public function __construct(
        protected AdmissionRepositoryInterface $admissions,
        protected GuardianRepositoryInterface $guardians,
        protected StudentService $students,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->admissions->paginate($filters, $perPage);
    }

    public function show(Admission $admission): Admission
    {
        return $this->admissions->findOrFail($admission->id);
    }

    public function create(AdmissionData $data): Admission
    {
        return DB::transaction(fn (): Admission => $this->admissions->create($data));
    }

    public function update(Admission $admission, AdmissionData $data): Admission
    {
        return DB::transaction(fn (): Admission => $this->admissions->update($admission, $data));
    }

    public function delete(Admission $admission): void
    {
        DB::transaction(function () use ($admission): void {
            $this->admissions->delete($admission);
        });
    }

    public function submit(Admission $admission): Admission
    {
        return $this->transition($admission, 'submitted', [
            'submitted_at' => now(),
        ]);
    }

    public function review(Admission $admission, int $reviewedBy, ?string $remarks = null): Admission
    {
        return $this->transition($admission, 'under_review', [
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'remarks' => $remarks ?? $admission->remarks,
        ]);
    }

    public function approve(Admission $admission, int $reviewedBy, ?string $remarks = null): Admission
    {
        return $this->transition($admission, 'approved', [
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'remarks' => $remarks ?? $admission->remarks,
        ]);
    }

    public function reject(Admission $admission, int $reviewedBy, ?string $remarks = null): Admission
    {
        return $this->transition($admission, 'rejected', [
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'remarks' => $remarks ?? $admission->remarks,
        ]);
    }

    public function waitlist(Admission $admission, int $reviewedBy, ?string $remarks = null): Admission
    {
        return $this->transition($admission, 'waitlisted', [
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'remarks' => $remarks ?? $admission->remarks,
        ]);
    }

    public function convertToStudent(Admission $admission, array $payload, int $performedBy): Admission
    {
        return DB::transaction(function () use ($admission, $payload, $performedBy): Admission {
            /** @var Guardian|null $guardian */
            $guardian = null;

            if (!empty($payload['guardian_id'])) {
                $guardian = Guardian::query()->findOrFail($payload['guardian_id']);
            } elseif ($admission->guardian_phone || $admission->guardian_email) {
                $guardian = Guardian::query()
                    ->when($admission->guardian_phone, fn ($query, $phone) => $query->where('phone', $phone))
                    ->when($admission->guardian_email, fn ($query, $email) => $query->orWhere('email', $email))
                    ->first();
            }

            if (! $guardian) {
                $nameParts = preg_split('/\s+/', trim((string) $admission->guardian_name)) ?: [];
                $guardian = $this->guardians->create(GuardianData::fromArray([
                    'school_id' => $admission->school_id,
                    'first_name' => $nameParts[0] ?? $admission->guardian_name,
                    'last_name' => count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : null,
                    'phone' => $admission->guardian_phone,
                    'email' => $admission->guardian_email,
                    'relationship_type' => 'Guardian',
                    'address_line1' => $admission->address_line1,
                    'address_line2' => $admission->address_line2,
                    'city' => $admission->city,
                    'state' => $admission->state,
                    'country' => $admission->country,
                    'postal_code' => $admission->postal_code,
                    'status' => 'active',
                ]));
            }

            $student = $this->students->create(
                StudentData::fromArray([
                    'school_id' => $admission->school_id,
                    'admission_no' => $payload['admission_no'],
                    'roll_no' => $payload['roll_no'] ?? null,
                    'first_name' => $admission->first_name,
                    'middle_name' => $admission->middle_name,
                    'last_name' => $admission->last_name,
                    'gender' => $admission->gender,
                    'date_of_birth' => optional($admission->date_of_birth)->toDateString(),
                    'admission_date' => now()->toDateString(),
                    'joining_date' => $payload['joining_date'] ?? now()->toDateString(),
                    'current_status' => $payload['current_status'] ?? 'active',
                    'address' => [
                        'line1' => $admission->address_line1,
                        'line2' => $admission->address_line2,
                        'city' => $admission->city,
                        'state' => $admission->state,
                        'country' => $admission->country,
                        'postal_code' => $admission->postal_code,
                    ],
                    'guardians' => [[
                        'id' => $guardian->id,
                        'relationship' => 'Guardian',
                        'relationship_label' => 'Guardian',
                        'is_primary' => true,
                        'is_emergency_contact' => true,
                        'pickup_authorized' => true,
                    ]],
                    'enrollment' => [
                        'academic_year_id' => $admission->academic_year_id,
                        'school_class_id' => $admission->applied_class_id,
                        'section_id' => $payload['section_id'] ?? $admission->section_id,
                        'roll_number' => $payload['roll_no'] ?? null,
                        'status' => 'enrolled',
                        'joined_on' => $payload['joining_date'] ?? now()->toDateString(),
                        'enrollment_date' => $payload['joining_date'] ?? now()->toDateString(),
                        'is_current' => true,
                    ],
                    'admission' => [],
                ]),
                $performedBy,
            );

            return $this->admissions->update($admission, AdmissionData::fromArray([
                ...$admission->toArray(),
                'student_id' => $student->id,
                'application_status' => 'converted',
                'status' => 'admitted',
                'admitted_on' => now()->toDateString(),
                'reviewed_by' => $performedBy,
                'reviewed_at' => now(),
            ]));
        });
    }

    protected function transition(Admission $admission, string $status, array $attributes = []): Admission
    {
        return DB::transaction(function () use ($admission, $status, $attributes): Admission {
            return $this->admissions->update($admission, AdmissionData::fromArray([
                ...$admission->toArray(),
                'application_status' => $status,
                ...$attributes,
            ]));
        });
    }
}
