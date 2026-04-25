<?php

namespace Tests\Feature\Timetable;

use App\Models\AcademicYear;
use App\Models\Timetable\TimetableVersion;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableFoundationApiTest extends TestCase
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

    public function test_can_create_timetable_room(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->postJson('/api/v1/timetable/rooms', [
            'name' => 'Computer Lab 2',
            'code' => 'LAB-COMP-2',
            'room_type' => 'lab',
            'capacity' => 28,
            'building' => 'Tech Block',
            'floor' => '2',
            'status' => 'active',
        ])->assertCreated()->assertJsonPath('data.code', 'LAB-COMP-2');

        $this->assertDatabaseHas('timetable_rooms', [
            'code' => 'LAB-COMP-2',
            'room_type' => 'lab',
        ]);
    }

    public function test_period_sequence_must_be_unique_per_tenant(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->postJson('/api/v1/timetable/periods', [
            'name' => 'Duplicate Sequence Slot',
            'code' => 'DUP-SEQ',
            'start_time' => '11:00',
            'end_time' => '11:45',
            'sequence' => 2,
            'is_break' => false,
            'break_type' => null,
            'status' => 'active',
        ])->assertStatus(422);
    }

    public function test_publishing_version_archives_existing_published_version_for_same_year(): void
    {
        $headers = $this->authenticate();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('code', 'AY-2026-27')->firstOrFail();

        $firstResponse = $this->withHeaders($headers)->postJson('/api/v1/timetable/versions', [
            'academic_year_id' => $academicYear->id,
            'name' => 'Published Version Alpha',
            'code' => 'TT-PUB-1',
            'effective_from' => '2026-04-01',
            'effective_to' => null,
            'status' => 'published',
        ])->assertCreated();

        $secondResponse = $this->withHeaders($headers)->postJson('/api/v1/timetable/versions', [
            'academic_year_id' => $academicYear->id,
            'name' => 'Draft Version Beta',
            'code' => 'TT-DRF-2',
            'effective_from' => '2026-05-01',
            'effective_to' => null,
            'status' => 'draft',
        ])->assertCreated();

        $firstId = $firstResponse->json('data.id');
        $secondId = $secondResponse->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/timetable/versions/{$secondId}/publish", [
            'remarks' => 'Publishing improved weekly draft.',
        ])->assertOk()->assertJsonPath('data.status', 'published');

        $this->assertDatabaseHas('timetable_versions', [
            'id' => $firstId,
            'status' => 'archived',
        ]);
        $this->assertDatabaseHas('timetable_versions', [
            'id' => $secondId,
            'status' => 'published',
        ]);
    }

    public function test_duplicate_version_creates_draft_copy(): void
    {
        $headers = $this->authenticate();
        $version = TimetableVersion::withoutGlobalScopes()->where('code', 'TT-2026-MAIN')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/timetable/versions/{$version->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.code', 'TT-2026-MAIN-COPY');

        $this->assertDatabaseHas('timetable_versions', [
            'code' => 'TT-2026-MAIN-COPY',
            'status' => 'draft',
        ]);
    }

    public function test_options_endpoint_returns_timetable_dropdown_data(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->getJson('/api/v1/timetable/options')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'academic_years',
                    'periods',
                    'rooms',
                    'versions',
                    'classes',
                    'sections',
                    'staff',
                    'subjects',
                ],
            ]);
    }
}
