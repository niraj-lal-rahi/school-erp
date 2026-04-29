<?php

namespace App\Repositories\Eloquent\Reports;

use App\Models\Reports\DashboardWidget;
use App\Models\Reports\UserDashboardLayout;
use App\Repositories\Contracts\Reports\DashboardRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function paginateWidgets(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->widgetQuery()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function allWidgets(array $filters = []): Collection
    {
        return $this->widgetQuery()
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->orderBy('id')
            ->get();
    }

    public function findWidgetOrFail(int $id): DashboardWidget
    {
        return $this->widgetQuery()->findOrFail($id);
    }

    public function createWidget(array $attributes): DashboardWidget
    {
        $dashboardWidget = DashboardWidget::create($attributes);

        return $this->findWidgetOrFail($dashboardWidget->id);
    }

    public function updateWidget(DashboardWidget $dashboardWidget, array $attributes): DashboardWidget
    {
        $dashboardWidget->update($attributes);

        return $this->findWidgetOrFail($dashboardWidget->id);
    }

    public function deleteWidget(DashboardWidget $dashboardWidget): void
    {
        $dashboardWidget->delete();
    }

    public function findLayout(string $userType, int $userId): ?UserDashboardLayout
    {
        return UserDashboardLayout::query()
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->first();
    }

    public function upsertLayout(string $userType, int $userId, array $layout, int $schoolId): UserDashboardLayout
    {
        UserDashboardLayout::query()->updateOrCreate(
            [
                'school_id' => $schoolId,
                'user_type' => $userType,
                'user_id' => $userId,
            ],
            [
                'layout' => $layout,
            ],
        );

        return UserDashboardLayout::query()
            ->where('school_id', $schoolId)
            ->where('user_type', $userType)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    protected function widgetQuery(): Builder
    {
        return DashboardWidget::query();
    }
}
