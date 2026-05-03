<?php

namespace App\Services\AcademicManagement;

use App\Models\Section;
use App\Repositories\Contracts\AcademicManagement\SectionRepositoryInterface;
use App\Services\Cache\TenantCacheService;
use Illuminate\Support\Collection;

class SectionService extends AbstractAcademicCrudService
{
    public function __construct(
        SectionRepositoryInterface $repository,
        protected TenantCacheService $cache,
    ) {
        parent::__construct($repository);
    }

    public function cachedOptions(int $schoolId): Collection
    {
        return $this->cache->remember(
            'sections',
            $schoolId,
            ['options'],
            now()->addMinutes(30),
            fn () => Section::query()
                ->select(['id', 'school_id', 'school_class_id', 'name', 'code', 'capacity', 'class_teacher_id', 'status'])
                ->orderBy('school_class_id')
                ->orderBy('name')
                ->get()
        );
    }
}
