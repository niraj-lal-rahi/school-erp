<?php

namespace App\Repositories\Contracts\Reports;

use App\Models\Reports\ReportDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportDefinitionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): ReportDefinition;

    public function create(array $attributes): ReportDefinition;

    public function update(ReportDefinition $reportDefinition, array $attributes): ReportDefinition;

    public function delete(ReportDefinition $reportDefinition): void;
}
