<?php

namespace App\Repositories\Contracts\Reports;

use App\Models\Reports\DashboardWidget;
use App\Models\Reports\UserDashboardLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function paginateWidgets(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function allWidgets(array $filters = []): Collection;

    public function findWidgetOrFail(int $id): DashboardWidget;

    public function createWidget(array $attributes): DashboardWidget;

    public function updateWidget(DashboardWidget $dashboardWidget, array $attributes): DashboardWidget;

    public function deleteWidget(DashboardWidget $dashboardWidget): void;

    public function findLayout(string $userType, int $userId): ?UserDashboardLayout;

    public function upsertLayout(string $userType, int $userId, array $layout, int $schoolId): UserDashboardLayout;
}
