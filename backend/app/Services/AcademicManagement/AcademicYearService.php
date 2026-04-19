<?php

namespace App\Services\AcademicManagement;

use App\Models\AcademicYear;
use App\Repositories\Contracts\AcademicManagement\AcademicYearRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AcademicYearService extends AbstractAcademicCrudService
{
    public function __construct(
        protected AcademicYearRepositoryInterface $academicYears,
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

            return $this->academicYears->create($payload);
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

            return $this->academicYears->update($model, $payload);
        });
    }

    public function updateStatus(AcademicYear $academicYear, array $payload): AcademicYear
    {
        /** @var AcademicYear $academicYear */
        return $this->update($academicYear, $payload);
    }
}
