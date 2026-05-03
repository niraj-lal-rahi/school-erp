<?php

namespace App\Policies\Workflows;

use App\Models\User;
use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowInstance;
use App\Models\Workflows\WorkflowStep;
use App\Models\Workflows\WorkflowStepInstance;

class WorkflowPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, WorkflowDefinition|WorkflowStep|WorkflowInstance|WorkflowStepInstance $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, WorkflowDefinition|WorkflowStep|WorkflowInstance|WorkflowStepInstance $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canManage($user);
    }

    public function delete(User $user, WorkflowDefinition|WorkflowStep|WorkflowInstance|WorkflowStepInstance $resource): bool
    {
        return $this->sameTenant($user, $resource->school_id) && $this->canManage($user);
    }

    public function activate(User $user, WorkflowDefinition $workflowDefinition): bool
    {
        return $this->update($user, $workflowDefinition);
    }

    public function deactivate(User $user, WorkflowDefinition $workflowDefinition): bool
    {
        return $this->update($user, $workflowDefinition);
    }

    public function start(User $user, ?WorkflowDefinition $workflowDefinition = null): bool
    {
        if ($workflowDefinition && ! $this->sameTenant($user, $workflowDefinition->school_id)) {
            return false;
        }

        return $this->canManage($user);
    }

    public function cancel(User $user, WorkflowInstance $workflowInstance): bool
    {
        return $this->sameTenant($user, $workflowInstance->school_id) && $this->canManage($user);
    }

    protected function canView(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('workflows.view')
            || $user->hasPermission('workflows.manage');
    }

    protected function canManage(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('workflows.manage');
    }

    protected function sameTenant(User $user, ?int $schoolId): bool
    {
        return (int) $user->school_id === (int) $schoolId;
    }

    protected function isSuperAdmin(User $user): bool
    {
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'super_admin')
                    ->orWhere('roles.slug', 'super_admin');
            })
            ->exists();
    }

    protected function isTenantAdmin(User $user): bool
    {
        return $user->roles()
            ->withoutGlobalScopes()
            ->where(function ($query): void {
                $query->where('roles.code', 'tenant_admin')
                    ->orWhere('roles.slug', 'school-admin');
            })
            ->where('roles.school_id', $user->school_id)
            ->exists();
    }
}
