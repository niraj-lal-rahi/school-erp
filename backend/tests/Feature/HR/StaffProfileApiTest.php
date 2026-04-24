<?php

namespace Tests\Feature\HR;

use App\Models\HR\Staff;
use App\Support\Auth\JwtManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_authorized_user_can_manage_staff_profile_records(): void
    {
        Storage::fake('local');
        config()->set('filesystems.default', 'local');

        $headers = $this->authenticate();
        $staff = Staff::withoutGlobalScopes()->where('employee_code', 'EMP-0002')->firstOrFail();

        $this->withHeaders($headers)->post('/api/v1/hr/staff/'.$staff->id.'/upload-document', [
            'document_type' => 'resume',
            'title' => 'Updated Resume',
            'file' => UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf'),
            'issued_by' => 'Self',
            'verification_status' => 'verified',
        ])->assertCreated()->assertJsonPath('data.staff_id', $staff->id);

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff/'.$staff->id.'/emergency-contacts', [
            'contact_name' => 'Rahul Nair',
            'relationship' => 'Brother',
            'phone' => '9555555555',
            'is_primary' => true,
        ])->assertCreated()->assertJsonPath('data.contact_name', 'Rahul Nair');

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff/'.$staff->id.'/qualifications', [
            'degree' => 'B.Ed.',
            'institution' => 'Mysore University',
            'passing_year' => 2013,
            'percentage_or_grade' => 'A',
        ])->assertCreated()->assertJsonPath('data.degree', 'B.Ed.');

        $this->withHeaders($headers)->postJson('/api/v1/hr/staff/'.$staff->id.'/experiences', [
            'organization_name' => 'Greenwood Public School',
            'designation' => 'Senior Mathematics Teacher',
            'start_date' => '2025-04-01',
            'is_current' => true,
        ])->assertCreated()->assertJsonPath('data.is_current', true);

        $this->withHeaders($headers)->getJson('/api/v1/hr/staff/'.$staff->id)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'documents',
                    'emergency_contacts',
                    'qualifications',
                    'work_experiences',
                ],
            ]);
    }
}
