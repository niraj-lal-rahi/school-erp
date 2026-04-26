<?php

namespace App\Services\Transport;

use App\Models\StudentEnrollment;
use App\Models\Transport\StaffTransportAllocation;
use App\Models\Transport\StudentTransportAllocation;
use App\Models\Transport\TransportRouteStop;
use App\Repositories\Contracts\Transport\StaffTransportAllocationRepositoryInterface;
use App\Repositories\Contracts\Transport\StudentTransportAllocationRepositoryInterface;
use App\Repositories\Contracts\Transport\TransportRouteVehicleAssignmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportAllocationService
{
    public function __construct(
        protected StudentTransportAllocationRepositoryInterface $studentAllocations,
        protected StaffTransportAllocationRepositoryInterface $staffAllocations,
        protected TransportRouteVehicleAssignmentRepositoryInterface $routeAssignments,
    ) {
    }

    public function paginateStudentAllocations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->studentAllocations->paginate($filters, $perPage);
    }

    public function showStudentAllocation(StudentTransportAllocation $allocation): StudentTransportAllocation
    {
        return $this->studentAllocations->findOrFail($allocation->id);
    }

    public function createStudentAllocation(array $attributes): StudentTransportAllocation
    {
        return DB::transaction(function () use ($attributes): StudentTransportAllocation {
            $payload = $this->prepareStudentAllocation($attributes);
            $this->guardStudentAllocation($payload);

            return $this->studentAllocations->create($payload);
        });
    }

    public function updateStudentAllocation(
        StudentTransportAllocation $allocation,
        array $attributes,
    ): StudentTransportAllocation {
        return DB::transaction(function () use ($allocation, $attributes): StudentTransportAllocation {
            $payload = $this->prepareStudentAllocation(array_merge($allocation->only([
                'school_id',
                'student_id',
                'academic_year_id',
                'route_id',
                'route_vehicle_assignment_id',
                'pickup_stop_id',
                'drop_stop_id',
                'allocated_from',
                'allocated_to',
                'fare_amount',
                'status',
                'remarks',
            ]), $attributes));

            $this->guardStudentAllocation($payload, $allocation->id);

            return $this->studentAllocations->update($allocation, $payload);
        });
    }

    public function deleteStudentAllocation(StudentTransportAllocation $allocation): void
    {
        DB::transaction(function () use ($allocation): void {
            $this->studentAllocations->delete($allocation);
        });
    }

    public function paginateStaffAllocations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->staffAllocations->paginate($filters, $perPage);
    }

    public function showStaffAllocation(StaffTransportAllocation $allocation): StaffTransportAllocation
    {
        return $this->staffAllocations->findOrFail($allocation->id);
    }

    public function createStaffAllocation(array $attributes): StaffTransportAllocation
    {
        return DB::transaction(function () use ($attributes): StaffTransportAllocation {
            $payload = $this->prepareStaffAllocation($attributes);
            $this->guardStaffAllocation($payload);

            return $this->staffAllocations->create($payload);
        });
    }

    public function updateStaffAllocation(
        StaffTransportAllocation $allocation,
        array $attributes,
    ): StaffTransportAllocation {
        return DB::transaction(function () use ($allocation, $attributes): StaffTransportAllocation {
            $payload = $this->prepareStaffAllocation(array_merge($allocation->only([
                'school_id',
                'staff_id',
                'route_id',
                'route_vehicle_assignment_id',
                'pickup_stop_id',
                'drop_stop_id',
                'allocated_from',
                'allocated_to',
                'fare_amount',
                'status',
                'remarks',
            ]), $attributes));

            $this->guardStaffAllocation($payload, $allocation->id);

            return $this->staffAllocations->update($allocation, $payload);
        });
    }

    public function deleteStaffAllocation(StaffTransportAllocation $allocation): void
    {
        DB::transaction(function () use ($allocation): void {
            $this->staffAllocations->delete($allocation);
        });
    }

    protected function prepareStudentAllocation(array $attributes): array
    {
        $attributes['active_scope_key'] = ($attributes['status'] ?? null) === 'active' ? 'active' : null;

        return $attributes;
    }

    protected function prepareStaffAllocation(array $attributes): array
    {
        $attributes['active_scope_key'] = ($attributes['status'] ?? null) === 'active' ? 'active' : null;

        return $attributes;
    }

    protected function guardStudentAllocation(array $attributes, ?int $ignoreId = null): void
    {
        if (($attributes['status'] ?? null) === 'active') {
            $existing = $this->studentAllocations->activeForStudent(
                (int) $attributes['student_id'],
                (int) $attributes['academic_year_id'],
                $ignoreId,
            );

            if ($existing) {
                throw ValidationException::withMessages([
                    'student_id' => ['An active transport allocation already exists for this student in the selected academic year.'],
                ]);
            }
        }

        $enrollment = StudentEnrollment::query()
            ->where('school_id', $attributes['school_id'])
            ->where('student_id', $attributes['student_id'])
            ->where('academic_year_id', $attributes['academic_year_id'])
            ->where('is_current', true)
            ->first();

        if (! $enrollment) {
            throw ValidationException::withMessages([
                'student_id' => ['The selected student does not have a current enrollment for the selected academic year.'],
            ]);
        }

        $this->guardStopsAndAssignment($attributes);
    }

    protected function guardStaffAllocation(array $attributes, ?int $ignoreId = null): void
    {
        if (($attributes['status'] ?? null) === 'active') {
            $existing = $this->staffAllocations->activeForStaff((int) $attributes['staff_id'], $ignoreId);

            if ($existing) {
                throw ValidationException::withMessages([
                    'staff_id' => ['An active transport allocation already exists for this staff member.'],
                ]);
            }
        }

        $this->guardStopsAndAssignment($attributes);
    }

    protected function guardStopsAndAssignment(array $attributes): void
    {
        $routeId = (int) $attributes['route_id'];

        foreach (['pickup_stop_id', 'drop_stop_id'] as $field) {
            if (empty($attributes[$field])) {
                continue;
            }

            $stop = TransportRouteStop::query()->findOrFail($attributes[$field]);
            if ((int) $stop->route_id !== $routeId) {
                throw ValidationException::withMessages([
                    $field => ['The selected stop does not belong to the selected route.'],
                ]);
            }
        }

        if (! empty($attributes['route_vehicle_assignment_id'])) {
            $assignment = $this->routeAssignments->findOrFail((int) $attributes['route_vehicle_assignment_id']);

            if ((int) $assignment->route_id !== $routeId) {
                throw ValidationException::withMessages([
                    'route_vehicle_assignment_id' => ['The selected vehicle assignment does not belong to the selected route.'],
                ]);
            }
        }
    }
}
