<?php

namespace App\Services\Workflows;

use App\Events\Workflows\WorkflowStepApproved;
use App\Events\Workflows\WorkflowStepRejected;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Workflows\ApprovalRequest;
use App\Models\Workflows\WorkflowInstance;
use App\Models\Workflows\WorkflowStep;
use App\Models\Workflows\WorkflowStepInstance;
use App\Repositories\Contracts\Workflows\ApprovalRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function __construct(
        protected ApprovalRequestRepositoryInterface $approvals,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->approvals->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): ApprovalRequest
    {
        return $this->approvals->findOrFail($id);
    }

    public function pendingSummary(array $filters = [], int $perPage = 15): array
    {
        $baseFilters = array_merge($filters, ['status' => 'pending']);

        $query = ApprovalRequest::withoutGlobalScopes()
            ->when($filters['school_id'] ?? null, fn (Builder $builder, $value) => $builder->where('school_id', $value))
            ->when($filters['module'] ?? null, fn (Builder $builder, string $value) => $builder->where('module', $value))
            ->when($filters['reference_type'] ?? null, fn (Builder $builder, string $value) => $builder->where('reference_type', $value))
            ->when($filters['reference_id'] ?? null, fn (Builder $builder, $value) => $builder->where('reference_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('requested_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('requested_at', '<=', $value));

        return [
            'counts' => [
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
                'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            ],
            'items' => $this->paginate($baseFilters, $perPage),
        ];
    }

    public function ensureApprovalRequest(WorkflowInstance $instance, WorkflowStepInstance $stepInstance, WorkflowStep $step): ApprovalRequest
    {
        $existing = ApprovalRequest::query()
            ->where('school_id', $instance->school_id)
            ->where('workflow_instance_id', $instance->id)
            ->where('reference_type', $instance->reference_type)
            ->where('reference_id', $instance->reference_id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return $existing;
        }

        $assignment = $this->resolveApprover($instance->school_id, $step);
        $stepInstance->update([
            'assigned_to' => $assignment['approver_id'],
            'metadata' => array_merge($stepInstance->metadata ?? [], [
                'approver_role_id' => $assignment['approver_role_id'],
            ]),
        ]);

        return $this->approvals->create([
            'school_id' => $instance->school_id,
            'workflow_instance_id' => $instance->id,
            'module' => $instance->workflowDefinition?->module ?? 'general',
            'reference_type' => $instance->reference_type,
            'reference_id' => $instance->reference_id,
            'requested_by' => $instance->started_by,
            'approver_id' => $assignment['approver_id'],
            'approver_role_id' => $assignment['approver_role_id'],
            'status' => 'pending',
            'requested_at' => now(),
            'responded_at' => null,
            'remarks' => null,
        ]);
    }

    public function approve(ApprovalRequest $approvalRequest, User $approver, array $payload = []): ApprovalRequest
    {
        $this->guardApprovalAccess($approvalRequest, $approver, $approvalRequest->requested_by);

        return DB::transaction(function () use ($approvalRequest, $approver, $payload): ApprovalRequest {
            $approvalRequest = $this->approvals->update($approvalRequest, [
                'status' => 'approved',
                'responded_at' => now(),
                'remarks' => $payload['remarks'] ?? null,
            ]);

            $stepInstance = $this->resolvePendingStepInstance($approvalRequest);
            $stepInstance?->update([
                'status' => 'approved',
                'action_taken_by' => $approver->id,
                'action_taken_at' => now(),
                'remarks' => $payload['remarks'] ?? 'Approved.',
                'metadata' => array_merge($stepInstance->metadata ?? [], ['approval_status' => 'approved']),
            ]);

            $instance = $approvalRequest->workflowInstance?->fresh();
            if ($instance) {
                app(WorkflowRuntimeService::class)->advance($instance);
            }

            DB::afterCommit(fn () => event(new WorkflowStepApproved($approvalRequest)));

            return $approvalRequest->fresh(['workflowInstance', 'requester', 'approver', 'approverRole']);
        });
    }

    public function reject(ApprovalRequest $approvalRequest, User $approver, array $payload = []): ApprovalRequest
    {
        $this->guardApprovalAccess($approvalRequest, $approver, $approvalRequest->requested_by);

        return DB::transaction(function () use ($approvalRequest, $approver, $payload): ApprovalRequest {
            $approvalRequest = $this->approvals->update($approvalRequest, [
                'status' => 'rejected',
                'responded_at' => now(),
                'remarks' => $payload['remarks'] ?? 'Rejected.',
            ]);

            $stepInstance = $this->resolvePendingStepInstance($approvalRequest);
            $stepInstance?->update([
                'status' => 'rejected',
                'action_taken_by' => $approver->id,
                'action_taken_at' => now(),
                'remarks' => $payload['remarks'] ?? 'Rejected.',
                'metadata' => array_merge($stepInstance->metadata ?? [], ['approval_status' => 'rejected']),
            ]);

            $instance = $approvalRequest->workflowInstance?->fresh();
            if ($instance) {
                app(WorkflowRuntimeService::class)->cancel($instance, $approver, $payload['remarks'] ?? 'Workflow rejected.');
            }

            DB::afterCommit(fn () => event(new WorkflowStepRejected($approvalRequest)));

            return $approvalRequest->fresh(['workflowInstance', 'requester', 'approver', 'approverRole']);
        });
    }

    protected function resolveApprover(int $schoolId, WorkflowStep $step): array
    {
        if ($step->assigned_user_id) {
            return [
                'approver_id' => $step->assigned_user_id,
                'approver_role_id' => $step->assigned_role_id,
            ];
        }

        if (! $step->assigned_role_id) {
            throw ValidationException::withMessages([
                'workflow_step_id' => ['Approval steps require an assigned role or user.'],
            ]);
        }

        $role = Role::query()
            ->visibleInTenant($schoolId)
            ->find($step->assigned_role_id);

        if (! $role) {
            throw ValidationException::withMessages([
                'assigned_role_id' => ['The assigned approval role is not available in this tenant.'],
            ]);
        }

        $approverUserId = UserRole::query()
            ->where('role_id', $role->id)
            ->where(function ($query) use ($schoolId): void {
                $query->whereNull('school_id')
                    ->orWhere('school_id', $schoolId);
            })
            ->value('user_id');

        if (! $approverUserId) {
            throw ValidationException::withMessages([
                'assigned_role_id' => ['No user is currently assigned to the selected approver role.'],
            ]);
        }

        return [
            'approver_id' => (int) $approverUserId,
            'approver_role_id' => $role->id,
        ];
    }

    protected function resolvePendingStepInstance(ApprovalRequest $approvalRequest): ?WorkflowStepInstance
    {
        $instance = $approvalRequest->workflowInstance?->loadMissing('stepInstances.workflowStep');

        if (! $instance) {
            return null;
        }

        return $instance->stepInstances
            ->sortBy(fn (WorkflowStepInstance $stepInstance) => $stepInstance->workflowStep?->sequence ?? PHP_INT_MAX)
            ->first(fn (WorkflowStepInstance $stepInstance) => $stepInstance->status === 'pending');
    }

    protected function guardApprovalAccess(ApprovalRequest $approvalRequest, User $actor, ?int $requestedBy): void
    {
        if ($requestedBy && $requestedBy === $actor->id) {
            throw ValidationException::withMessages([
                'approval' => ['Users cannot approve or reject their own workflow request.'],
            ]);
        }

        if ($approvalRequest->approver_id && $approvalRequest->approver_id !== $actor->id) {
            throw ValidationException::withMessages([
                'approval' => ['This approval request is assigned to another approver.'],
            ]);
        }
    }
}
