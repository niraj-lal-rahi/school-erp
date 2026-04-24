<?php

namespace App\Repositories\Eloquent\HR;

use App\DataTransferObjects\HR\StaffNoteData;
use App\Models\HR\Staff;
use App\Models\HR\StaffNote;
use App\Repositories\Contracts\HR\StaffNoteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StaffNoteRepository implements StaffNoteRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return StaffNote::query()
            ->with('creator')
            ->when($filters['staff_id'] ?? null, fn ($query, int|string $staffId) => $query->where('staff_id', $staffId))
            ->orderByDesc('id')
            ->get();
    }

    public function create(Staff $staff, StaffNoteData $data): StaffNote
    {
        return $staff->notesEntries()->create($data->attributes + ['school_id' => $staff->school_id]);
    }

    public function update(StaffNote $note, StaffNoteData $data): StaffNote
    {
        $note->update($data->attributes);

        return $note->refresh()->load('creator');
    }

    public function delete(StaffNote $note): void
    {
        $note->delete();
    }

    public function allForStaff(Staff $staff): Collection
    {
        return $this->all(['staff_id' => $staff->id]);
    }
}
