<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\FeeStructureData;
use App\Models\Finance\FeeStructure;
use App\Repositories\Contracts\Finance\FeeStructureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeeStructureService
{
    public function __construct(
        protected FeeStructureRepositoryInterface $feeStructures,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->feeStructures->all($filters);
    }

    public function create(FeeStructureData $data): FeeStructure
    {
        $this->guardSectionMatchesClass($data->attributes);

        return DB::transaction(fn (): FeeStructure => $this->feeStructures->create($data));
    }

    public function update(FeeStructure $feeStructure, FeeStructureData $data): FeeStructure
    {
        $this->guardSectionMatchesClass($data->attributes);

        return DB::transaction(fn (): FeeStructure => $this->feeStructures->update($feeStructure, $data));
    }

    public function delete(FeeStructure $feeStructure): void
    {
        DB::transaction(fn (): bool => $feeStructure->delete());
    }

    protected function guardSectionMatchesClass(array $attributes): void
    {
        $sectionId = $attributes['section_id'] ?? null;
        $schoolClassId = $attributes['school_class_id'] ?? null;

        if (! $sectionId || ! $schoolClassId) {
            return;
        }

        $section = \App\Models\Section::query()->find($sectionId);
        if (! $section || (int) $section->school_class_id !== (int) $schoolClassId) {
            throw ValidationException::withMessages([
                'section_id' => 'The selected section does not belong to the selected class.',
            ]);
        }
    }
}
