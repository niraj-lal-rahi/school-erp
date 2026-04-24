<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\StaffEmergencyContactData;
use App\Models\HR\Staff;
use App\Models\HR\StaffEmergencyContact;
use Illuminate\Database\Eloquent\Collection;

interface StaffEmergencyContactRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(Staff $staff, StaffEmergencyContactData $data): StaffEmergencyContact;

    public function update(StaffEmergencyContact $contact, StaffEmergencyContactData $data): StaffEmergencyContact;

    public function delete(StaffEmergencyContact $contact): void;

    public function allForStaff(Staff $staff): Collection;
}
