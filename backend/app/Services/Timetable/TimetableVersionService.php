<?php

namespace App\Services\Timetable;

use App\DataTransferObjects\Timetable\TimetableVersionData;
use App\Events\Timetable\TimetableVersionPublished;
use App\Models\Timetable\TimetableVersion;
use App\Repositories\Contracts\Timetable\TimetableVersionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TimetableVersionService
{
    public function __construct(
        protected TimetableVersionRepositoryInterface $versions,
        protected TimetablePublishLogService $logs,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->versions->all($filters);
    }

    public function create(TimetableVersionData $data): TimetableVersion
    {
        return DB::transaction(function () use ($data): TimetableVersion {
            $version = $this->versions->create($data);

            $this->logs->create([
                'school_id' => $version->school_id,
                'timetable_version_id' => $version->id,
                'action' => 'created',
                'performed_by' => $version->created_by,
                'remarks' => 'Timetable version created.',
            ]);

            return $version;
        });
    }

    public function update(TimetableVersion $version, TimetableVersionData $data, int $performedBy): TimetableVersion
    {
        return DB::transaction(function () use ($version, $data, $performedBy): TimetableVersion {
            if ($version->status === 'published') {
                abort(422, 'Published timetable versions cannot be edited directly.');
            }

            $version = $this->versions->update($version, $data);

            $this->logs->create([
                'school_id' => $version->school_id,
                'timetable_version_id' => $version->id,
                'action' => 'updated',
                'performed_by' => $performedBy,
                'remarks' => 'Timetable version updated.',
            ]);

            return $version;
        });
    }

    public function publish(TimetableVersion $version, int $performedBy, ?string $remarks = null): TimetableVersion
    {
        return DB::transaction(function () use ($version, $performedBy, $remarks): TimetableVersion {
            $this->versions->archiveOtherPublished($version->school_id, $version->academic_year_id, $version->id);

            $version = $this->versions->update($version, TimetableVersionData::fromArray([
                'school_id' => $version->school_id,
                'academic_year_id' => $version->academic_year_id,
                'name' => $version->name,
                'code' => $version->code,
                'effective_from' => $version->effective_from?->toDateString(),
                'effective_to' => $version->effective_to?->toDateString(),
                'status' => 'published',
                'published_at' => now(),
                'created_by' => $version->created_by,
            ]));

            $this->logs->create([
                'school_id' => $version->school_id,
                'timetable_version_id' => $version->id,
                'action' => 'published',
                'performed_by' => $performedBy,
                'remarks' => $remarks ?: 'Timetable version published.',
            ]);

            event(new TimetableVersionPublished($version, $performedBy, $remarks));

            return $version;
        });
    }

    public function archive(TimetableVersion $version, int $performedBy, ?string $remarks = null): TimetableVersion
    {
        return DB::transaction(function () use ($version, $performedBy, $remarks): TimetableVersion {
            $version = $this->versions->update($version, TimetableVersionData::fromArray([
                'school_id' => $version->school_id,
                'academic_year_id' => $version->academic_year_id,
                'name' => $version->name,
                'code' => $version->code,
                'effective_from' => $version->effective_from?->toDateString(),
                'effective_to' => $version->effective_to?->toDateString(),
                'status' => 'archived',
                'published_at' => $version->published_at,
                'created_by' => $version->created_by,
            ]));

            $this->logs->create([
                'school_id' => $version->school_id,
                'timetable_version_id' => $version->id,
                'action' => 'archived',
                'performed_by' => $performedBy,
                'remarks' => $remarks ?: 'Timetable version archived.',
            ]);

            return $version;
        });
    }

    public function duplicate(TimetableVersion $version, int $performedBy): TimetableVersion
    {
        return DB::transaction(function () use ($version, $performedBy): TimetableVersion {
            $duplicate = $this->versions->create(TimetableVersionData::fromArray([
                'school_id' => $version->school_id,
                'academic_year_id' => $version->academic_year_id,
                'name' => $version->name.' Copy',
                'code' => $version->code.'-COPY',
                'effective_from' => $version->effective_from?->toDateString(),
                'effective_to' => $version->effective_to?->toDateString(),
                'status' => 'draft',
                'published_at' => null,
                'created_by' => $performedBy,
            ]));

            $this->logs->create([
                'school_id' => $duplicate->school_id,
                'timetable_version_id' => $duplicate->id,
                'action' => 'created',
                'performed_by' => $performedBy,
                'remarks' => 'Timetable version duplicated from version '.$version->id.'.',
            ]);

            return $duplicate;
        });
    }

    public function delete(TimetableVersion $version): void
    {
        DB::transaction(function () use ($version): void {
            $this->versions->delete($version);
        });
    }
}
