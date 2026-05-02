<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TenantRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Tenant;

    public function create(array $attributes): Tenant;

    public function update(Tenant $tenant, array $attributes): Tenant;

    public function delete(Tenant $tenant): void;

    public function restore(int $id): Tenant;

    public function findByDomain(string $domain): ?Tenant;
}
