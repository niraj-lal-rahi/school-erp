<?php

namespace App\Repositories\Contracts\Saas;

use App\Models\Saas\SubscriptionPlan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SubscriptionPlanRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function all(array $filters = []): Collection;

    public function findOrFail(int $id): SubscriptionPlan;

    public function create(array $attributes): SubscriptionPlan;

    public function update(SubscriptionPlan $plan, array $attributes): SubscriptionPlan;

    public function delete(SubscriptionPlan $plan): void;
}
