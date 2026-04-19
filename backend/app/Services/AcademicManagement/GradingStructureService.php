<?php

namespace App\Services\AcademicManagement;

use App\Models\AcademicManagement\GradingStructure;
use App\Repositories\Contracts\AcademicManagement\GradingStructureRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GradingStructureService extends AbstractAcademicCrudService
{
    public function __construct(
        protected GradingStructureRepositoryInterface $gradingStructures,
    ) {
        parent::__construct($gradingStructures);
    }

    public function create(array $payload): Model
    {
        return DB::transaction(function () use ($payload): Model {
            $scaleItems = $payload['scale_items'] ?? [];
            unset($payload['scale_items']);

            /** @var GradingStructure $gradingStructure */
            $gradingStructure = $this->gradingStructures->create($payload);
            $gradingStructure->scaleItems()->createMany($scaleItems);

            return $gradingStructure->load('scaleItems');
        });
    }

    public function update(Model $model, array $payload): Model
    {
        return DB::transaction(function () use ($model, $payload): Model {
            $scaleItems = $payload['scale_items'] ?? [];
            unset($payload['scale_items']);

            /** @var GradingStructure $gradingStructure */
            $gradingStructure = $this->gradingStructures->update($model, $payload);
            $gradingStructure->scaleItems()->delete();
            $gradingStructure->scaleItems()->createMany($scaleItems);

            return $gradingStructure->load('scaleItems');
        });
    }
}
