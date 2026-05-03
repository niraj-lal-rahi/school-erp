<?php

namespace App\Policies\Workflows;

use App\Models\User;
use App\Models\UserRole;
use App\Models\Workflows\ApprovalRequest;

class ApprovalPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canApprove($user) || $this->canManage($user);
    }

    public function view(User $user, ApprovalRequest $approvalRequest): bool
    {
        return $this->sameTenant($user, $approvalRequest->school_id)
            && ($this->canManage($user) || $this->isAssignedApprover($user, $approvalRequest));
    }

    public function approve(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (! $this->sameTenant($user, $approvalRequest->school_id)) {
            return false;
        }

        if ($approvalRequest->requested_by && (int) $approvalRequest->requested_by === (int) $user->id) {
            return false;
        }

        return $this->canManage($user)
            || ($this->canApprove($user) && $this->isAssignedApprover($user, $approvalRequest));
    }

    public function reject(User $user, ApprovalRequest $approvalRequest): bool
    {
        return $this->approve($user, $approvalRequest);
    }

    protected function isAssignedApprover(User $user, ApprovalRequest $approvalRequest): bool
    {
        if ($approvalRequest->approver_id) {
            return (int) $approvalRequest->approver_id === (int) $user->id;
        }

        if (! $approvalRequest->approver_role_id) {
            return false;
        }

        return UserRole::query()
            ->where('user_id', $user->id)
            ->where('role_id', $approvalRequest->approver_role_id)
            ->where(function ($query) use ($user): void {
                $query->whereNull('school_id')
                    ->orWhere('school_id', $user->school_id);
            })
            ->exists();
    }

    protected function canApprove(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isTenantAdmin($user)
            || $user->hasPermission('workflows.approve')
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
