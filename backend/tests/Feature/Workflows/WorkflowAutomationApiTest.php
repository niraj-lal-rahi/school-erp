<?php

namespace Tests\Feature\Workflows;

use App\Models\Communication\NotificationLog;
use App\Models\Finance\FeeInvoice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use App\Models\Workflows\ApprovalRequest;
use App\Models\Workflows\AutomationRule;
use App\Models\Workflows\ReminderLog;
use App\Models\Workflows\ReminderRule;
use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowInstance;
use App\Services\Rbac\AccessControlService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class WorkflowAutomationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function headersFor(string $email = 'admin@greenwood.edu', string $tenantCode = 'greenwood'): array
    {
        if (! School::withoutGlobalScopes()->where('code', 'greenwood')->exists()) {
            $this->seed();
        }

        app('auth')->forgetGuards();

        $user = User::withoutGlobalScopes()->where('email', $email)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => $tenantCode,
        ];
    }

    protected function greenwoodSchool(): School
    {
        return School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
    }

    protected function greenwoodStudent(): \App\Models\Student
    {
        return \App\Models\Student::withoutGlobalScopes()->where('school_id', $this->greenwoodSchool()->id)->firstOrFail();
    }

    protected function createApproverForRole(string $roleCode, string $email, string $name): User
    {
        $school = $this->greenwoodSchool();
        $role = Role::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('code', $roleCode)
            ->firstOrFail();
        $role->permissions()->syncWithoutDetaching(
            Permission::query()
                ->whereIn('code', ['workflows.view', 'workflows.approve'])
                ->pluck('id')
                ->all()
        );

        $user = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => $email,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Workflow',
                'last_name' => 'Approver',
                'name' => $name,
                'phone' => '9777700001',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $role->id => ['school_id' => $school->id],
        ]);

        app(\App\Services\Rbac\AccessControlService::class)->clearUserCache($user);

        return $user;
    }

    protected function createApprovalWorkflow(string $code, ?int $approverRoleId = null, ?int $approverUserId = null): WorkflowDefinition
    {
        $school = $this->greenwoodSchool();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();

        $workflow = WorkflowDefinition::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'name' => $code.' Workflow',
            'code' => $code,
            'module' => 'general',
            'description' => 'Test workflow.',
            'trigger_type' => 'manual',
            'trigger_event' => null,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $workflow->steps()->create([
            'school_id' => $school->id,
            'step_name' => 'Approval Step',
            'step_type' => 'approval',
            'sequence' => 1,
            'config' => ['message' => 'Please approve.'],
            'assigned_role_id' => $approverRoleId,
            'assigned_user_id' => $approverUserId,
            'status' => 'active',
        ]);

        return $workflow->fresh(['steps']);
    }

    public function test_tenant_admin_can_create_workflow_definition(): void
    {
        $headers = $this->headersFor();

        $response = $this->withHeaders($headers)->postJson('/api/v1/workflows/definitions', [
            'name' => 'Transport Deviation Approval',
            'code' => 'TRANSPORT-DEVIATION',
            'module' => 'transport',
            'description' => 'Handles deviation approvals.',
            'trigger_type' => 'manual',
            'status' => 'draft',
        ])->assertCreated();

        $this->assertDatabaseHas('workflow_definitions', [
            'id' => $response->json('data.id'),
            'code' => 'TRANSPORT-DEVIATION',
            'school_id' => $this->greenwoodSchool()->id,
        ]);
    }

    public function test_assigned_approver_can_approve_workflow_step(): void
    {
        $adminHeaders = $this->headersFor();
        $approver = $this->createApproverForRole('principal', 'approver@greenwood.edu', 'Workflow Approver');
        $workflow = $this->createApprovalWorkflow('APPROVAL-FLOW-OK', null, $approver->id);
        $student = $this->greenwoodStudent();

        $startResponse = $this->withHeaders($adminHeaders)->postJson('/api/v1/workflows/start', [
            'workflow_definition_id' => $workflow->id,
            'reference_type' => \App\Models\Student::class,
            'reference_id' => $student->id,
            'metadata' => ['reason' => 'Approval flow test'],
        ])->assertCreated();

        $instanceId = $startResponse->json('data.id');
        $approvalRequest = ApprovalRequest::withoutGlobalScopes()
            ->where('workflow_instance_id', $instanceId)
            ->where('status', 'pending')
            ->firstOrFail();
        $this->assertSame($approver->id, (int) $approvalRequest->approver_id);
        $this->assertTrue(app(AccessControlService::class)->checkPermission($approver->fresh(), 'workflows.approve'));
        $this->assertTrue($approver->fresh()->can('approve', $approvalRequest->fresh()));

        $principalHeaders = $this->headersFor('approver@greenwood.edu');

        $this->withHeaders($principalHeaders)->postJson("/api/v1/workflows/approvals/{$approvalRequest->id}/approve", [
            'remarks' => 'Approved by principal.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $instanceId,
            'status' => 'completed',
        ]);
    }

    public function test_rejection_flow_cancels_workflow_instance(): void
    {
        $adminHeaders = $this->headersFor();
        $approver = $this->createApproverForRole('principal', 'rejector@greenwood.edu', 'Workflow Rejector');
        $workflow = $this->createApprovalWorkflow('APPROVAL-FLOW-REJECT', null, $approver->id);
        $student = $this->greenwoodStudent();

        $startResponse = $this->withHeaders($adminHeaders)->postJson('/api/v1/workflows/start', [
            'workflow_definition_id' => $workflow->id,
            'reference_type' => \App\Models\Student::class,
            'reference_id' => $student->id,
        ])->assertCreated();

        $approvalRequest = ApprovalRequest::withoutGlobalScopes()
            ->where('workflow_instance_id', $startResponse->json('data.id'))
            ->where('status', 'pending')
            ->firstOrFail();
        $this->assertSame($approver->id, (int) $approvalRequest->approver_id);
        $this->assertTrue(app(AccessControlService::class)->checkPermission($approver->fresh(), 'workflows.approve'));
        $this->assertTrue($approver->fresh()->can('reject', $approvalRequest->fresh()));

        $principalHeaders = $this->headersFor('rejector@greenwood.edu');

        $this->withHeaders($principalHeaders)->postJson("/api/v1/workflows/approvals/{$approvalRequest->id}/reject", [
            'remarks' => 'Insufficient information.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $startResponse->json('data.id'),
            'status' => 'cancelled',
        ]);
    }

    public function test_can_execute_automation_rule_and_log_actions(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();

        $rule = AutomationRule::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'name' => 'Workflow Test Automation',
            'code' => 'WORKFLOW-TEST-AUTOMATION',
            'module' => 'general',
            'trigger_type' => 'condition',
            'trigger_event' => null,
            'schedule_expression' => null,
            'conditions' => [
                'field' => 'metadata.level',
                'operator' => '=',
                'value' => 'high',
            ],
            'actions' => [[
                'type' => 'task',
                'title' => 'Follow-up Task',
                'description' => 'Created from automation.',
                'assignee_id' => $admin->id,
            ]],
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this->withHeaders($headers)->postJson("/api/v1/workflows/automations/{$rule->id}/run", [
            'metadata' => ['level' => 'high'],
            'reference_type' => \App\Models\Student::class,
            'reference_id' => $this->greenwoodStudent()->id,
        ])->assertCreated();

        $this->assertDatabaseHas('automation_runs', [
            'id' => $response->json('data.id'),
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('automation_action_logs', [
            'automation_rule_id' => $rule->id,
            'status' => 'success',
            'action_type' => 'task',
        ]);
    }

    public function test_can_process_fee_reminders(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();
        $invoice = FeeInvoice::withoutGlobalScopes()->where('school_id', $school->id)->firstOrFail();
        $invoice->update([
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'overdue',
            'balance_amount' => max((float) $invoice->balance_amount, 100),
        ]);

        $response = $this->withHeaders($headers)->postJson('/api/v1/workflows/reminders/process-due')
            ->assertOk();

        $this->assertGreaterThan(0, (int) $response->json('data.processed'));
        $this->assertDatabaseHas('reminder_logs', [
            'school_id' => $school->id,
            'status' => 'sent',
        ]);
    }

    public function test_workflow_and_reminder_actions_integrate_with_communication_notifications(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();
        $invoice = FeeInvoice::withoutGlobalScopes()->where('school_id', $school->id)->firstOrFail();
        $invoice->update([
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'overdue',
            'balance_amount' => max((float) $invoice->balance_amount, 100),
        ]);

        $beforeCount = NotificationLog::withoutGlobalScopes()->count();

        $this->withHeaders($headers)->postJson('/api/v1/workflows/reminders/process-due')->assertOk();

        $this->assertGreaterThan($beforeCount, NotificationLog::withoutGlobalScopes()->count());
    }

    public function test_tenant_isolation_blocks_other_tenant_from_viewing_workflows(): void
    {
        $this->seed();

        $greenwoodSchool = $this->greenwoodSchool();
        WorkflowDefinition::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $greenwoodSchool->id,
                'code' => 'TENANT-ISOLATION-WORKFLOW',
            ],
            [
                'name' => 'Tenant Isolation Workflow',
                'module' => 'general',
                'description' => 'Isolation test workflow.',
                'trigger_type' => 'manual',
                'trigger_event' => null,
                'status' => 'active',
                'created_by' => User::withoutGlobalScopes()->where('school_id', $greenwoodSchool->id)->where('email', 'admin@greenwood.edu')->firstOrFail()->id,
            ]
        );

        $riverside = School::withoutGlobalScopes()->where('code', 'riverside')->firstOrFail();
        $tenantRole = Role::withoutGlobalScopes()->firstOrCreate(
            [
                'school_id' => $riverside->id,
                'slug' => 'school-admin',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Tenant Administrator',
                'code' => 'tenant_admin',
                'scope' => 'tenant',
                'description' => 'Riverside tenant administrator.',
                'role_type' => 'tenant',
                'is_default' => true,
                'status' => 'active',
            ]
        );
        $tenantRole->permissions()->sync(
            Permission::query()->pluck('id')->all()
        );

        $riversideUser = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $riverside->id,
                'email' => 'admin@riverside.edu',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Riverside',
                'last_name' => 'Admin',
                'name' => 'Riverside Admin',
                'phone' => '9555000003',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $riversideUser->roles()->syncWithoutDetaching([
            $tenantRole->id => ['school_id' => $riverside->id],
        ]);

        $headers = $this->headersFor('admin@riverside.edu', 'riverside');

        $response = $this->withHeaders($headers)->getJson('/api/v1/workflows/definitions')->assertOk();

        collect($response->json('data'))->each(function (array $item) use ($riverside): void {
            $this->assertSame($riverside->id, $item['school_id']);
        });
    }

    public function test_user_without_workflow_permissions_cannot_view_workflows(): void
    {
        $this->seed();

        $school = $this->greenwoodSchool();
        $user = User::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'school_id' => $school->id,
            'first_name' => 'Limited',
            'last_name' => 'Workflow',
            'name' => 'Limited Workflow User',
            'email' => 'limited.workflow@greenwood.edu',
            'phone' => '9555000004',
            'password' => Hash::make('password123'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $token = app(JwtManager::class)->issueAccessToken($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->getJson('/api/v1/workflows/definitions')->assertForbidden();
    }
}
