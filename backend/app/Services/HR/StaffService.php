<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffData;
use App\Models\HR\Staff;
use App\Repositories\Contracts\HR\StaffRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StaffService
{
    public function __construct(
        protected StaffRepositoryInterface $staff,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->staff->paginate($filters, $perPage);
    }

    public function show(Staff $staff): Staff
    {
        return $this->staff->findOrFail($staff->id)->load([
            'documents',
            'emergencyContacts',
            'qualifications',
            'workExperiences',
            'attendanceRecords',
            'bankDetails',
            'statusHistory.performer',
            'notesEntries.creator',
        ]);
    }

    public function create(StaffData $data, int $performedBy): Staff
    {
        return DB::transaction(fn (): Staff => $this->staff->create(StaffData::fromArray([
            ...$data->attributes,
            'created_by' => $performedBy,
            'updated_by' => $performedBy,
        ])));
    }

    public function update(Staff $staff, StaffData $data, int $performedBy): Staff
    {
        return DB::transaction(fn (): Staff => $this->staff->update($staff, StaffData::fromArray([
            ...$data->attributes,
            'created_by' => $staff->created_by,
            'updated_by' => $performedBy,
        ])));
    }

    public function delete(Staff $staff): void
    {
        DB::transaction(fn (): bool => $staff->delete());
    }
}
