<?php

namespace App\Repositories\Eloquent\Attendance;

use App\DataTransferObjects\Attendance\AttendanceImportData;
use App\Models\Attendance\AttendanceImport;
use App\Repositories\Contracts\Attendance\AttendanceImportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceImportRepository implements AttendanceImportRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return AttendanceImport::query()
            ->with('uploader')
            ->when($filters['import_type'] ?? null, fn ($query, string $type) => $query->where('import_type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('import_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('import_date', '<=', $date))
            ->orderByDesc('import_date')
            ->orderByDesc('id')
            ->get();
    }

    public function create(AttendanceImportData $data): AttendanceImport
    {
        return AttendanceImport::create($data->attributes)->load('uploader');
    }

    public function update(AttendanceImport $attendanceImport, AttendanceImportData $data): AttendanceImport
    {
        $attendanceImport->update($data->attributes);

        return $attendanceImport->refresh()->load('uploader');
    }
}
