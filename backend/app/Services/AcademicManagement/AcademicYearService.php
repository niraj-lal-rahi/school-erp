<?php

namespace App\Services\AcademicManagement;

use App\Models\AcademicYear;
use App\Repositories\Contracts\AcademicManagement\AcademicYearRepositoryInterface;
use App\Services\Cache\TenantCacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AcademicYearService extends AbstractAcademicCrudService
{
    public function __construct(
        protected AcademicYearRepositoryInterface $academicYears,
        protected TenantCacheService $cache,
    ) {
        parent::__construct($academicYears);
    }

    public function create(array $payload): Model
    {
        return DB::transaction(function () use ($payload): Model {
            if (($payload['is_active'] ?? false) === true) {
                AcademicYear::query()->update([
                    'is_active' => false,
                    'is_current' => false,
                    'status' => 'inactive',
                ]);
            }

            $payload['is_current'] = $payload['is_active'] ?? false;
            $year = $this->academicYears->create($payload);
            $this->clearAcademicCache((int) $year->school_id);

            return $year;
        });
    }

    public function update(Model $model, array $payload): Model
    {
        return DB::transaction(function () use ($model, $payload): Model {
            if (($payload['is_active'] ?? false) === true) {
                AcademicYear::query()
                    ->where('id', '!=', $model->getKey())
                    ->update([
                        'is_active' => false,
                        'is_current' => false,
                        'status' => 'inactive',
                    ]);
            }

            $payload['is_current'] = $payload['is_active'] ?? false;
            $year = $this->academicYears->update($model, $payload);
            $this->clearAcademicCache((int) $year->school_id);

            return $year;
        });
    }

    public function updateStatus(AcademicYear $academicYear, array $payload): AcademicYear
    {
        /** @var AcademicYear $academicYear */
        return $this->update($academicYear, $payload);
    }

    public function cachedOptions(int $schoolId): Collection
    {
        return $this->cache->remember(
            'academic-years',
            $schoolId,
            ['options'],
            now()->addMinutes(30),
            fn () => AcademicYear::query()
                ->select(['id', 'school_id', 'name', 'code', 'start_date', 'end_date', 'is_active', 'is_current', 'status'])
                ->orderByDesc('is_current')
                ->orderByDesc('is_active')
                ->orderByDesc('start_date')
                ->get()
        );
    }
}
