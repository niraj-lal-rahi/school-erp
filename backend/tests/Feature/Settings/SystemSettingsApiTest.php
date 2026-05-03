<?php

namespace Tests\Feature\Settings;

use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\Settings\FeatureFlag;
use App\Models\Settings\Setting;
use App\Models\User;
use App\Services\Rbac\AccessControlService;
use App\Services\Settings\FeatureFlagService;
use App\Services\Settings\SettingService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SystemSettingsApiTest extends TestCase
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

    public function test_global_setting_fallback_is_returned_for_tenant(): void
    {
        $headers = $this->headersFor();

        $this->withHeaders($headers)
            ->getJson('/api/v1/settings/by-key/app_name')
            ->assertOk()
            ->assertJsonPath('data.key', 'app_name')
            ->assertJsonPath('data.value', 'School ERP');
    }

    public function test_tenant_override_wins_over_global_setting(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        Setting::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'scope' => 'tenant',
                'key' => 'app_name',
            ],
            [
                'group_id' => null,
                'value' => 'Greenwood ERP',
                'value_type' => 'string',
                'is_sensitive' => false,
                'is_public' => true,
                'description' => 'Tenant application name.',
            ]
        );

        $this->withHeaders($headers)
            ->getJson('/api/v1/settings/by-key/app_name')
            ->assertOk()
            ->assertJsonPath('data.value', 'Greenwood ERP');
    }

    public function test_sensitive_setting_value_is_encrypted_and_masked(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        $response = $this->withHeaders($headers)->postJson('/api/v1/settings', [
            'school_id' => $school->id,
            'key' => 'integrations.smtp_password',
            'value' => 'super-secret',
            'value_type' => 'string',
            'scope' => 'tenant',
            'is_sensitive' => true,
            'is_public' => false,
            'description' => 'SMTP password',
        ])->assertCreated()
            ->assertJsonPath('data.value', null)
            ->assertJsonPath('data.is_sensitive', true);

        $settingId = $response->json('data.id');
        $stored = Setting::withoutGlobalScopes()->findOrFail($settingId);

        $this->assertNotSame('super-secret', $stored->value);
        $this->assertSame(
            'super-secret',
            app(SettingService::class)->getByKey('integrations.smtp_password', $school->id)
        );
    }

    public function test_public_config_hides_sensitive_values(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        Setting::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'scope' => 'tenant',
                'key' => 'branding.support_phone',
            ],
            [
                'value' => '+91-9999999999',
                'value_type' => 'string',
                'is_sensitive' => false,
                'is_public' => true,
                'description' => 'Public support phone.',
            ]
        );

        Setting::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'scope' => 'tenant',
                'key' => 'payments.api_secret',
            ],
            [
                'value' => Crypt::encryptString('sensitive-secret'),
                'value_type' => 'string',
                'is_sensitive' => true,
                'is_public' => false,
                'description' => 'Payment secret.',
            ]
        );

        $response = $this->withHeaders($headers)
            ->getJson('/api/v1/settings/public-config')
            ->assertOk();

        $settings = $response->json('data.settings');

        $this->assertSame('+91-9999999999', $settings['branding.support_phone'] ?? null);
        $this->assertArrayNotHasKey('payments.api_secret', $settings);
    }

    public function test_feature_flag_check_uses_tenant_override(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        $feature = FeatureFlag::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('feature_code', 'payments')
            ->where('module', 'payments')
            ->firstOrFail();

        $this->assertTrue(app(FeatureFlagService::class)->checkFeatureEnabled('payments', 'payments', $school->id));

        $this->withHeaders($headers)
            ->postJson("/api/v1/settings/features/{$feature->id}/disable")
            ->assertOk()
            ->assertJsonPath('data.is_enabled', false);

        $this->assertFalse(app(FeatureFlagService::class)->checkFeatureEnabled('payments', 'payments', $school->id));
    }

    public function test_can_update_branding_settings(): void
    {
        $headers = $this->headersFor();

        $this->withHeaders($headers)->putJson('/api/v1/settings/branding', [
            'school_name' => 'Greenwood Academy',
            'primary_color' => '#112233',
            'secondary_color' => '#445566',
            'accent_color' => '#778899',
            'footer_text' => 'Learning together',
        ])->assertOk()
            ->assertJsonPath('data.school_name', 'Greenwood Academy')
            ->assertJsonPath('data.primary_color', '#112233');

        $this->withHeaders($headers)->getJson('/api/v1/settings/public-config')
            ->assertOk()
            ->assertJsonPath('data.branding.school_name', 'Greenwood Academy');
    }

    public function test_can_update_localization_settings(): void
    {
        $headers = $this->headersFor();

        $this->withHeaders($headers)->putJson('/api/v1/settings/localization', [
            'timezone' => 'Asia/Dubai',
            'locale' => 'en',
            'currency' => 'AED',
            'currency_symbol' => 'AED',
            'date_format' => 'Y-m-d',
        ])->assertOk()
            ->assertJsonPath('data.timezone', 'Asia/Dubai')
            ->assertJsonPath('data.currency', 'AED');
    }

    public function test_can_update_security_settings(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        $this->withHeaders($headers)->putJson('/api/v1/settings/security', [
            'password_min_length' => 12,
            'password_requires_symbol' => true,
            'two_factor_enabled' => true,
        ])->assertOk()
            ->assertJsonPath('data.password_min_length', 12)
            ->assertJsonPath('data.two_factor_enabled', true);

        $this->assertSame(12, app(\App\Services\Settings\SecuritySettingService::class)->authRules($school->id)['password_min_length']);
    }

    public function test_setting_update_creates_audit_log(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        $response = $this->withHeaders($headers)->postJson('/api/v1/settings', [
            'school_id' => $school->id,
            'key' => 'notifications.sender_email',
            'value' => 'noreply@greenwood.edu',
            'value_type' => 'string',
            'scope' => 'tenant',
            'is_sensitive' => false,
            'is_public' => false,
            'description' => 'Sender email',
        ])->assertCreated();

        $settingId = $response->json('data.id');

        $this->assertDatabaseHas('setting_audit_logs', [
            'school_id' => $school->id,
            'setting_type' => 'setting',
            'setting_key' => 'notifications.sender_email',
        ]);

        $this->withHeaders($headers)->getJson('/api/v1/settings/audit-logs')
            ->assertOk()
            ->assertJsonFragment(['setting_key' => 'notifications.sender_email']);

        $this->assertDatabaseHas('settings', [
            'id' => $settingId,
            'key' => 'notifications.sender_email',
        ]);
    }

    public function test_tenant_isolation_blocks_other_tenant_setting_update(): void
    {
        $headers = $this->headersFor();
        $school = $this->greenwoodSchool();

        $setting = Setting::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'scope' => 'tenant',
                'key' => 'general.campus_code',
            ],
            [
                'value' => 'GW-01',
                'value_type' => 'string',
                'is_sensitive' => false,
                'is_public' => false,
                'description' => 'Campus code',
            ]
        );

        [, $otherUser] = $this->createOtherTenantAndAdmin();
        $otherHeaders = $this->headersFor($otherUser->email, 'riverdale');

        $this->withHeaders($otherHeaders)
            ->putJson("/api/v1/settings/{$setting->id}", [
                'value' => 'RV-99',
            ])
            ->assertNotFound();
    }
}
