<?php

namespace App\Policies\Reports;

use App\Models\Reports\DashboardWidget;
use App\Models\Reports\UserDashboardLayout;
use App\Models\User;

class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view') || $user->hasPermission('reports.manage');
    }

    public function view(User $user, DashboardWidget|UserDashboardLayout $dashboardResource): bool
    {
        return $user->school_id === $dashboardResource->school_id
            && ($user->hasPermission('reports.view') || $user->hasPermission('reports.manage'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reports.manage');
    }

    public function update(User $user, DashboardWidget|UserDashboardLayout $dashboardResource): bool
    {
        return $user->school_id === $dashboardResource->school_id
            && $user->hasPermission('reports.manage');
    }

    public function delete(User $user, DashboardWidget|UserDashboardLayout $dashboardResource): bool
    {
        return $user->school_id === $dashboardResource->school_id
            && $user->hasPermission('reports.manage');
    }

    public function run(User $user, DashboardWidget|UserDashboardLayout|null $dashboardResource = null): bool
    {
        if ($dashboardResource && $user->school_id !== $dashboardResource->school_id) {
            return false;
        }

        return $user->hasPermission('reports.manage') || $user->hasPermission('reports.run');
    }

    public function export(User $user, DashboardWidget|UserDashboardLayout|null $dashboardResource = null): bool
    {
        if ($dashboardResource && $user->school_id !== $dashboardResource->school_id) {
            return false;
        }

        return $user->hasPermission('reports.view')
            || $user->hasPermission('reports.manage')
            || $user->hasPermission('reports.export');
    }
}
