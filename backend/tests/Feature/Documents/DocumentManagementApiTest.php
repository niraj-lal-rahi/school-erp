<?php

namespace Tests\Feature\Documents;

use App\Models\Documents\Document;
use App\Models\Documents\DocumentCategory;
use App\Models\Documents\DocumentFolder;
use App\Models\Documents\DocumentTag;
use App\Models\Documents\DocumentVerification;
use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\Rbac\AccessControlService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class DocumentManagementApiTest extends TestCase
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

    protected function createViewerUser(string $email = 'documents.viewer@greenwood.edu'): User
    {
        $school = $this->greenwoodSchool();

        $role = Role::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'document_viewer',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Document Viewer',
                'slug' => 'document-viewer',
                'scope' => 'tenant',
                'description' => 'Can view documents if explicitly permitted.',
                'role_type' => 'tenant',
                'is_default' => false,
                'status' => 'active',
            ]
        );

        $permissionIds = Permission::query()
            ->whereIn('code', ['documents.view'])
            ->pluck('id')
            ->all();

        $role->permissions()->sync($permissionIds);

        $user = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => $email,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Document',
                'last_name' => 'Viewer',
                'name' => 'Document Viewer',
                'phone' => '9777711111',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $role->id => ['school_id' => $school->id],
        ]);

        app(AccessControlService::class)->clearUserCache($user);

        return $user;
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
                'slug' => 'school-admin',
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

    protected function uploadDocument(array $headers, array $overrides = []): array
    {
        Storage::fake('local');

        $school = $this->greenwoodSchool();
        $student = Student::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->firstOrFail();
        $category = DocumentCategory::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'STUDENT-ID-PROOF')->firstOrFail();
        $folder = DocumentFolder::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'STUDENT-RECORDS')->firstOrFail();
        $tag = DocumentTag::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'IDENTITY')->firstOrFail();

        $file = UploadedFile::fake()->create('identity-proof.pdf', 120, 'application/pdf');

        $response = $this->withHeaders($headers)->post('/api/v1/documents', array_merge([
            'school_id' => $school->id,
            'category_id' => $category->id,
            'folder_id' => $folder->id,
            'owner_type' => 'student',
            'owner_id' => $student->id,
            'title' => 'Uploaded Identity Proof',
            'description' => 'Uploaded during test.',
            'document_no' => 'TEST-DOC-001',
            'issue_date' => now()->subDays(5)->toDateString(),
            'expiry_date' => now()->addMonth()->toDateString(),
            'disk' => 'local',
            'tags' => [$tag->id],
            'permissions' => [[
                'permission_type' => 'owner',
                'can_view' => true,
                'can_download' => true,
                'can_update' => false,
                'can_delete' => false,
                'can_verify' => false,
            ]],
            'file' => $file,
        ], $overrides));

        return [$response, $school, $student];
    }

    public function test_can_upload_document(): void
    {
        $headers = $this->headersFor();

        [$response, $school] = $this->uploadDocument($headers);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Uploaded Identity Proof')
            ->assertJsonPath('data.current_file.original_file_name', 'identity-proof.pdf');

        $this->assertDatabaseHas('documents', [
            'school_id' => $school->id,
            'document_no' => 'TEST-DOC-001',
            'title' => 'Uploaded Identity Proof',
        ]);
    }

    public function test_download_permission_check_blocks_non_permitted_user(): void
    {
        $adminHeaders = $this->headersFor();
        [$response] = $this->uploadDocument($adminHeaders);
        $documentId = $response->json('data.id');

        $viewer = $this->createViewerUser();
        $viewerHeaders = $this->headersFor($viewer->email);

        $this->withHeaders($viewerHeaders)
            ->get("/api/v1/documents/{$documentId}/download")
            ->assertForbidden();
    }

    public function test_can_upload_new_document_version(): void
    {
        $headers = $this->headersFor();
        [$response] = $this->uploadDocument($headers);
        $documentId = $response->json('data.id');

        $versionFile = UploadedFile::fake()->create('identity-proof-v2.pdf', 150, 'application/pdf');

        $this->withHeaders($headers)
            ->post("/api/v1/documents/{$documentId}/versions", [
                'school_id' => $this->greenwoodSchool()->id,
                'disk' => 'local',
                'file' => $versionFile,
            ])
            ->assertCreated()
            ->assertJsonPath('data.version_no', 2)
            ->assertJsonPath('data.is_current', true);

        $this->assertDatabaseHas('document_files', [
            'document_id' => $documentId,
            'version_no' => 1,
            'is_current' => false,
        ]);
    }

    public function test_can_verify_document(): void
    {
        $headers = $this->headersFor();
        [$response] = $this->uploadDocument($headers, ['document_no' => 'TEST-DOC-VERIFY']);
        $documentId = $response->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/documents/{$documentId}/verify", [
                'school_id' => $this->greenwoodSchool()->id,
                'remarks' => 'Verified by admin.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'verified');

        $this->assertDatabaseHas('documents', [
            'id' => $documentId,
            'verification_status' => 'verified',
        ]);
    }

    public function test_can_reject_document(): void
    {
        $headers = $this->headersFor();
        [$response] = $this->uploadDocument($headers, ['document_no' => 'TEST-DOC-REJECT']);
        $documentId = $response->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/documents/{$documentId}/reject", [
                'school_id' => $this->greenwoodSchool()->id,
                'remarks' => 'Document image is unreadable.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('documents', [
            'id' => $documentId,
            'verification_status' => 'rejected',
        ]);
    }

    public function test_expiry_detection_report_returns_expired_documents(): void
    {
        $headers = $this->headersFor();
        [$response] = $this->uploadDocument($headers, [
            'document_no' => 'TEST-DOC-EXPIRED',
            'expiry_date' => now()->subDay()->toDateString(),
        ]);
        $documentId = $response->json('data.id');

        $this->withHeaders($headers)->getJson('/api/v1/documents/reports/expired')
            ->assertOk()
            ->assertJsonFragment(['id' => $documentId]);
    }

    public function test_tenant_isolation_blocks_other_tenant_document_access(): void
    {
        $headers = $this->headersFor();
        [$response] = $this->uploadDocument($headers, ['document_no' => 'TEST-DOC-TENANT']);
        $documentId = $response->json('data.id');

        [, $otherUser] = $this->createOtherTenantAndAdmin();
        $otherHeaders = $this->headersFor($otherUser->email, 'riverdale');

        $this->withHeaders($otherHeaders)
            ->getJson("/api/v1/documents/{$documentId}")
            ->assertNotFound();
    }

    public function test_folder_service_blocks_circular_nesting(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        $parentResponse = $this->withHeaders($headers)->postJson('/api/v1/documents/folders', [
            'school_id' => $school->id,
            'name' => 'Parent Test Folder',
            'code' => 'PARENT-TEST-FOLDER',
            'visibility' => 'private',
        ])->assertCreated();

        $parentId = $parentResponse->json('data.id');

        $childResponse = $this->withHeaders($headers)->postJson('/api/v1/documents/folders', [
            'school_id' => $school->id,
            'parent_id' => $parentId,
            'name' => 'Child Test Folder',
            'code' => 'CHILD-TEST-FOLDER',
            'visibility' => 'private',
        ])->assertCreated();

        $childId = $childResponse->json('data.id');

        $this->withHeaders($headers)->putJson("/api/v1/documents/folders/{$parentId}", [
            'school_id' => $school->id,
            'parent_id' => $childId,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_can_filter_documents_by_tag(): void
    {
        $headers = $this->headersFor();
        [$response] = $this->uploadDocument($headers, ['document_no' => 'TEST-DOC-TAG']);
        $documentId = $response->json('data.id');
        $tag = DocumentTag::withoutGlobalScopes()->where('school_id', $this->greenwoodSchool()->id)->where('code', 'IDENTITY')->firstOrFail();

        $this->withHeaders($headers)->getJson('/api/v1/documents?tags[]='.$tag->id)
            ->assertOk()
            ->assertJsonFragment(['id' => $documentId]);
    }

    public function test_document_upload_creates_audit_log(): void
    {
        $headers = $this->headersFor();
        [$response] = $this->uploadDocument($headers, ['document_no' => 'TEST-DOC-AUDIT']);
        $documentId = $response->json('data.id');

        $this->assertDatabaseHas('document_audit_logs', [
            'document_id' => $documentId,
            'action' => 'uploaded',
        ]);

        $this->withHeaders($headers)->getJson("/api/v1/documents/{$documentId}/audit-logs")
            ->assertOk()
            ->assertJsonFragment(['action' => 'uploaded']);
    }
}
