<?php

namespace Tests\Feature\Tenant;

use App\Models\Documents\Document;
use App\Models\Finance\FeeInvoice;
use App\Models\Permission;
use App\Models\Reports\ReportDefinition;
use App\Models\Reports\ReportRun;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Models\Workflows\ApprovalRequest;
use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowInstance;
use App\Models\Workflows\WorkflowStep;
use App\Models\Workflows\WorkflowStepInstance;
use App\Services\Rbac\AccessControlService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTestSuite extends TestCase
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

    protected function greenwoodAdmin(): User
    {
        return User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
    }

    protected function createOtherTenantAndAdmin(): array
    {
        $school = School::withoutGlobalScopes()->updateOrCreate(
            ['code' => 'riverdale'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Riverdale Public School',
                'slug' => 'riverdale-public-school',
                'domain' => 'riverdale.local',
                'timezone' => 'Asia/Calcutta',
                'locale' => 'en',
                'status' => 'active',
                'settings' => ['currency' => 'INR', 'country' => 'IN'],
                'storage_disk' => 'local',
            ]
        );

        $role = Role::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'slug' => 'school-admin',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Tenant Administrator',
                'code' => 'tenant_admin',
                'scope' => 'tenant',
                'description' => 'Tenant administrator.',
                'role_type' => 'tenant',
                'is_default' => true,
                'status' => 'active',
            ]
        );

        $role->permissions()->sync(Permission::query()->pluck('id')->all());

        $user = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'admin@riverdale.edu',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Riverdale',
                'last_name' => 'Admin',
                'name' => 'Riverdale Admin',
                'phone' => '9888800001',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $role->id => ['school_id' => $school->id],
        ]);

        app(AccessControlService::class)->clearUserCache($user);

        return [$school, $user];
    }

    protected function createGreenwoodDocument(): Document
    {
        $school = $this->greenwoodSchool();
        $admin = $this->greenwoodAdmin();

        return Document::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'owner_type' => 'general',
            'owner_id' => null,
            'title' => 'Isolation Test Document',
            'description' => 'Tenant isolation test document.',
            'document_no' => 'ISO-DOC-001',
            'verification_status' => 'pending',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
    }

    protected function createGreenwoodApprovalRequest(): ApprovalRequest
    {
        $school = $this->greenwoodSchool();
        $admin = $this->greenwoodAdmin();
        $student = Student::withoutGlobalScopes()->where('school_id', $school->id)->firstOrFail();

        $workflow = WorkflowDefinition::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'name' => 'Isolation Workflow',
            'code' => 'ISOLATION-WORKFLOW',
            'module' => 'general',
            'description' => 'Tenant isolation approval test.',
            'trigger_type' => 'manual',
            'trigger_event' => null,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $step = WorkflowStep::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'workflow_definition_id' => $workflow->id,
            'step_name' => 'Approval Step',
            'step_type' => 'approval',
            'sequence' => 1,
            'config' => ['message' => 'Approve request'],
            'assigned_user_id' => $admin->id,
            'status' => 'active',
        ]);

        $instance = WorkflowInstance::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'workflow_definition_id' => $workflow->id,
            'reference_type' => Student::class,
            'reference_id' => $student->id,
            'current_step_id' => $step->id,
            'status' => 'in_progress',
            'started_by' => $admin->id,
            'started_at' => now(),
            'metadata' => ['source' => 'tenant-isolation-test'],
        ]);

        WorkflowStepInstance::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'workflow_instance_id' => $instance->id,
            'workflow_step_id' => $step->id,
            'assigned_to' => $admin->id,
            'status' => 'pending',
            'metadata' => ['approver_role_id' => null],
        ]);

        return ApprovalRequest::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'workflow_instance_id' => $instance->id,
            'module' => 'general',
            'reference_type' => Student::class,
            'reference_id' => $student->id,
            'requested_by' => $admin->id,
            'approver_id' => $admin->id,
            'approver_role_id' => null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
    }

    protected function createGreenwoodReportRun(): ReportRun
    {
        $school = $this->greenwoodSchool();
        $admin = $this->greenwoodAdmin();

        $definition = ReportDefinition::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'name' => 'Isolation Report',
            'code' => 'ISOLATION-REPORT',
            'module' => 'general',
            'description' => 'Tenant isolation report.',
            'query_config' => ['source' => 'students'],
            'default_filters' => ['status' => 'active'],
            'is_system' => false,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        return ReportRun::withoutGlobalScopes()->create([
            'school_id' => $school->id,
            'report_definition_id' => $definition->id,
            'run_type' => 'manual',
            'status' => 'completed',
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
            'file_type' => 'json',
            'parameters' => ['source' => 'tenant-isolation-test'],
            'initiated_by' => $admin->id,
        ]);
    }

    public function test_user_from_tenant_a_cannot_access_tenant_b_students(): void
    {
        $this->seed();
        $student = Student::withoutGlobalScopes()->where('school_id', $this->greenwoodSchool()->id)->firstOrFail();
        [, $otherUser] = $this->createOtherTenantAndAdmin();

        $this->withHeaders($this->headersFor($otherUser->email, 'riverdale'))
            ->getJson("/api/v1/students/{$student->id}")
            ->assertNotFound();
    }

    public function test_tenant_a_cannot_access_tenant_b_invoices(): void
    {
        $this->seed();
        $invoice = FeeInvoice::withoutGlobalScopes()->where('school_id', $this->greenwoodSchool()->id)->firstOrFail();
        [, $otherUser] = $this->createOtherTenantAndAdmin();

        $this->withHeaders($this->headersFor($otherUser->email, 'riverdale'))
            ->getJson("/api/v1/finance/invoices/{$invoice->id}")
            ->assertNotFound();
    }

    public function test_tenant_a_cannot_access_tenant_b_documents(): void
    {
        $this->seed();
        $document = $this->createGreenwoodDocument();
        [, $otherUser] = $this->createOtherTenantAndAdmin();

        $this->withHeaders($this->headersFor($otherUser->email, 'riverdale'))
            ->getJson("/api/v1/documents/{$document->id}")
            ->assertNotFound();
    }

    public function test_tenant_a_cannot_approve_tenant_b_workflow(): void
    {
        $this->seed();
        $approvalRequest = $this->createGreenwoodApprovalRequest();
        [, $otherUser] = $this->createOtherTenantAndAdmin();

        $this->withHeaders($this->headersFor($otherUser->email, 'riverdale'))
            ->postJson("/api/v1/workflows/approvals/{$approvalRequest->id}/approve", [
                'remarks' => 'Attempted cross-tenant approval.',
            ])
            ->assertNotFound();
    }

    public function test_tenant_a_cannot_read_tenant_b_reports(): void
    {
        $this->seed();
        $reportRun = $this->createGreenwoodReportRun();
        [, $otherUser] = $this->createOtherTenantAndAdmin();

        $this->withHeaders($this->headersFor($otherUser->email, 'riverdale'))
            ->getJson("/api/v1/reports/runs/{$reportRun->id}")
            ->assertNotFound();
    }
}
