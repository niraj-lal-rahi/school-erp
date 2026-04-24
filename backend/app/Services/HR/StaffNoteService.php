<?php

namespace App\Services\HR;

use App\DataTransferObjects\HR\StaffNoteData;
use App\Models\HR\Staff;
use App\Models\HR\StaffNote;
use App\Repositories\Contracts\HR\StaffNoteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StaffNoteService
{
    public function __construct(
        protected StaffNoteRepositoryInterface $notes,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->notes->all($filters);
    }

    public function create(Staff $staff, StaffNoteData $data): StaffNote
    {
        return DB::transaction(fn (): StaffNote => $this->notes->create($staff, $data));
    }

    public function update(StaffNote $note, StaffNoteData $data): StaffNote
    {
        return DB::transaction(fn (): StaffNote => $this->notes->update($note, $data));
    }

    public function delete(StaffNote $note): void
    {
        DB::transaction(fn (): bool => $note->delete());
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->notes->allForStaff($staff);
    }
}
