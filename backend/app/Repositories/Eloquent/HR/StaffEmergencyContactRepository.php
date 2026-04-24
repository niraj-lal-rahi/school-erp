<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffEmergencyContactData;
use App\Models\HR\Staff;
use App\Models\HR\StaffEmergencyContact;
use App\Repositories\Contracts\HR\StaffEmergencyContactRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffEmergencyContactRepository implements StaffEmergencyContactRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StaffEmergencyContact::query()
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->orderByDesc('is_primary')
            ->orderBy('contact_name')
            ->get();
    }

    public function create(Staff $staff, StaffEmergencyContactData $data): StaffEmergencyContact
    {
        return $staff->emergencyContacts()->create($data->attributes + ['school_id' => $staff->school_id]);
    }

    public function update(StaffEmergencyContact $contact, StaffEmergencyContactData $data): StaffEmergencyContact
    {
        $contact->update($data->attributes);

        return $contact->refresh();
    }

    public function delete(StaffEmergencyContact $contact): void
    {
        $contact->delete();
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->all(['staff_id' => $staff->id]);
    }
}
