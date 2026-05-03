<?php

namespace App\Services\AcademicManagement;

use App\Models\SchoolClass;
use App\Repositories\Contracts\AcademicManagement\SchoolClassRepositoryInterface;
use App\Services\Cache\TenantCacheService;
use Illuminate\Support\Collection;

class SchoolClassService extends AbstractAcademicCrudService
{
    public function __construct(
        SchoolClassRepositoryInterface $repository,
        protected TenantCacheService $cache,
    ) {
        parent::__construct($repository);
    }

    public function cachedOptions(int $schoolId): Collection
    {
        return $this->cache->remember(
            'school-classes',
            $schoolId,
            ['options'],
            now()->addMinutes(30),
            fn () => SchoolClass::query()
                ->select(['id', 'school_id', 'academic_year_id', 'name', 'code', 'grade_level', 'level_order', 'sort_order', 'status'])
                ->orderBy('level_order')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }
}
