<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffEmergencyContactData;
use App\Models\HR\Staff;
use App\Models\HR\StaffEmergencyContact;
use App\Repositories\Contracts\HR\StaffEmergencyContactRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffEmergencyContactService
{
    public function __construct(
        protected StaffEmergencyContactRepositoryInterface $contacts,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->contacts->all($filters);
    }

    public function create(Staff $staff, StaffEmergencyContactData $data): StaffEmergencyContact
    {
        return DB::transaction(function () use ($staff, $data): StaffEmergencyContact {
            if (($data->attributes['is_primary'] ?? false) === true) {
                $this->clearPrimary($staff);
            }

            return $this->contacts->create($staff, $data);
        });
    }

    public function update(StaffEmergencyContact $contact, StaffEmergencyContactData $data): StaffEmergencyContact
    {
        return DB::transaction(function () use ($contact, $data): StaffEmergencyContact {
            if (($data->attributes['is_primary'] ?? false) === true) {
                $this->clearPrimary($contact->staff);
            }

            return $this->contacts->update($contact, $data);
        });
    }

    public function delete(StaffEmergencyContact $contact): void
    {
        DB::transaction(fn (): bool => $contact->delete());
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->contacts->allForStaff($staff);
    }

    protected function clearPrimary(Staff $staff): void
    {
        $staff->emergencyContacts()->update(['is_primary' => false]);
    }
}
