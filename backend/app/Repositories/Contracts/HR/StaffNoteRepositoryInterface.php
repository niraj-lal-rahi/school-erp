<?php

namespace App\Repositories\Contracts\HR;

use App\DataTransferObjects\HR\StaffNoteData;
use App\Models\HR\Staff;
use App\Models\HR\StaffNote;
use Illuminate\Database\Eloquent\Collection;

interface StaffNoteRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(Staff $staff, StaffNoteData $data): StaffNote;

    public function update(StaffNote $note, StaffNoteData $data): StaffNote;

    public function delete(StaffNote $note): void;

    public function allForStaff(Staff $staff): Collection;
}
