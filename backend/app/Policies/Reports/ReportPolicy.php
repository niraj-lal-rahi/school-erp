<?php

namespace App\Policies\Reports;

use App\Models\Reports\ReportDefinition;
use App\Models\Reports\ReportExport;
use App\Models\Reports\ReportRun;
use App\Models\Reports\ReportSchedule;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reports.view') || $user->hasPermission('reports.manage');
    }

    public function view(User $user, ReportDefinition|ReportSchedule|ReportRun|ReportExport $reportResource): bool
    {
        return $user->school_id === $reportResource->school_id
            && ($user->hasPermission('reports.view') || $user->hasPermission('reports.manage'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reports.manage');
    }

    public function update(User $user, ReportDefinition|ReportSchedule|ReportRun|ReportExport $reportResource): bool
    {
        return $user->school_id === $reportResource->school_id
            && $user->hasPermission('reports.manage');
    }

    public function delete(User $user, ReportDefinition|ReportSchedule|ReportRun|ReportExport $reportResource): bool
    {
        return $user->school_id === $reportResource->school_id
            && $user->hasPermission('reports.manage');
    }

    public function run(User $user, ?ReportDefinition $reportDefinition = null): bool
    {
        if ($reportDefinition && $user->school_id !== $reportDefinition->school_id) {
            return false;
        }

        return $user->hasPermission('reports.manage') || $user->hasPermission('reports.run');
    }

    public function export(User $user, ReportDefinition|ReportRun|ReportExport|null $reportResource = null): bool
    {
        if ($reportResource && $user->school_id !== $reportResource->school_id) {
            return false;
        }

        return $user->hasPermission('reports.view')
            || $user->hasPermission('reports.manage')
            || $user->hasPermission('reports.export');
    }
}
