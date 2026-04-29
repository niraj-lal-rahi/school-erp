<?php

namespace Tests\Feature\Reports;

use App\Jobs\Reports\ScheduleReportsJob;
use App\Models\Reports\ReportDefinition;
use App\Models\Reports\ReportSchedule;
use App\Models\School;
use App\Models\User;
use App\Services\Reports\ReportCacheService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportsAnalyticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(string $email = 'admin@greenwood.edu'): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', $email)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_authorized_user_can_run_report(): void
    {
        $headers = $this->authenticate();
        $definition = ReportDefinition::withoutGlobalScopes()->where('code', 'ATTENDANCE-SUMMARY')->firstOrFail();

        $response = $this->withHeaders($headers)->postJson('/api/v1/reports/run', [
            'report_definition_id' => $definition->id,
            'file_type' => 'json',
            'parameters' => [
                'date_from' => now()->startOfMonth()->toDateString(),
                'date_to' => now()->toDateString(),
            ],
        ])->assertCreated();

        $this->assertDatabaseHas('report_runs', [
            'id' => $response->json('data.id'),
            'report_definition_id' => $definition->id,
            'status' => 'completed',
            'run_type' => 'manual',
        ]);
    }

    public function test_completed_report_can_be_downloaded_as_export(): void
    {
        $headers = $this->authenticate();
        $definition = ReportDefinition::withoutGlobalScopes()->where('code', 'ATTENDANCE-SUMMARY')->firstOrFail();

        $runResponse = $this->withHeaders($headers)->postJson('/api/v1/reports/run', [
            'report_definition_id' => $definition->id,
            'file_type' => 'csv',
        ])->assertCreated();

        $runId = $runResponse->json('data.id');
        $run = \App\Models\Reports\ReportRun::withoutGlobalScopes()->with('exports')->findOrFail($runId);
        $export = $run->exports->firstOrFail();

        $this->withHeaders($headers)->get("/api/v1/reports/exports/{$export->id}/download")
            ->assertOk();

        $this->assertDatabaseHas('report_exports', [
            'id' => $export->id,
            'downloaded_count' => 1,
        ]);
    }

    public function test_active_schedule_can_dispatch_scheduled_report_run(): void
    {
        $headers = $this->authenticate();
        $admin = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $definition = ReportDefinition::withoutGlobalScopes()->where('code', 'EXAM-SUMMARY')->firstOrFail();

        $scheduleResponse = $this->withHeaders($headers)->postJson('/api/v1/reports/schedules', [
            'report_definition_id' => $definition->id,
            'schedule_type' => 'once',
            'schedule_config' => [
                'time' => now()->format('H:i'),
                'run_at' => now()->subMinute()->toDateTimeString(),
            ],
            'next_run_at' => now()->subMinute()->toDateTimeString(),
            'channel' => 'in_app',
            'recipients' => [[
                'user_type' => 'user',
                'user_id' => $admin->id,
            ]],
            'status' => 'active',
        ])->assertCreated();

        $schedule = ReportSchedule::withoutGlobalScopes()->findOrFail($scheduleResponse->json('data.id'));

        app(ScheduleReportsJob::class)->handle(app(\App\Repositories\Contracts\Reports\ReportScheduleRepositoryInterface::class));

        $this->assertDatabaseHas('report_runs', [
            'report_definition_id' => $definition->id,
            'run_type' => 'scheduled',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('report_schedules', [
            'id' => $schedule->id,
            'status' => 'paused',
        ]);
    }

    public function test_dashboard_can_be_loaded_with_seeded_widgets(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->getJson('/api/v1/reports/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'kpis' => [
                        'total_students',
                        'today_present_students',
                        'today_collection',
                    ],
                ],
            ]);

        $this->withHeaders($headers)->getJson('/api/v1/reports/widgets')
            ->assertOk()
            ->assertJsonCount(3, 'data.widgets');
    }

    public function test_report_cache_service_reuses_existing_cache_entry(): void
    {
        $this->seed();

        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $cache = app(ReportCacheService::class);
        $cacheKey = $cache->buildKey('reports.test.cache', ['module' => 'attendance']);
        $callbackCalls = 0;

        $first = $cache->remember($school->id, $cacheKey, function () use (&$callbackCalls): array {
            $callbackCalls++;

            return ['total' => 5];
        }, 600);

        $second = $cache->remember($school->id, $cacheKey, function () use (&$callbackCalls): array {
            $callbackCalls++;

            return ['total' => 99];
        }, 600);

        $this->assertSame(['total' => 5], $first);
        $this->assertSame(['total' => 5], $second);
        $this->assertSame(1, $callbackCalls);
        $this->assertDatabaseCount('report_cache', 1);
    }

    public function test_user_without_reports_permission_cannot_view_dashboard(): void
    {
        $this->seed();

        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $user = User::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'school_id' => $school->id,
            'first_name' => 'Limited',
            'last_name' => 'User',
            'name' => 'Limited User',
            'email' => 'limited.user@greenwood.edu',
            'phone' => '9988776654',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $token = app(JwtManager::class)->issueAccessToken($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->getJson('/api/v1/reports/dashboard')->assertForbidden();
    }
}
