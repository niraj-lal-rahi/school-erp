<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->create('setting_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'setting_groups_school_id_code_unique');
            $table->index(['school_id', 'status']);
        });

        $schema->create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('setting_groups')->nullOnDelete();
            $table->string('key');
            $table->longText('value')->nullable();
            $table->enum('value_type', ['string', 'integer', 'boolean', 'json', 'encrypted', 'file'])->default('string');
            $table->enum('scope', ['global', 'tenant'])->default('tenant');
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'scope', 'key'], 'settings_school_id_scope_key_unique');
            $table->index(['school_id', 'key']);
            $table->index(['scope', 'is_public']);
            $table->index(['group_id', 'is_sensitive']);
        });

        $schema->create('feature_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('feature_code');
            $table->string('module');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedTinyInteger('rollout_percentage')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'module', 'feature_code'], 'feature_flags_school_id_module_code_unique');
            $table->index(['school_id', 'module']);
            $table->index(['feature_code', 'is_enabled']);
        });

        $schema->create('branding_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('school_name');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('primary_color', 20)->nullable();
            $table->string('secondary_color', 20)->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->string('footer_text')->nullable();
            $table->longText('custom_css')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('school_id');
        });

        $schema->create('localization_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->string('locale')->default('en');
            $table->string('date_format')->default('d-m-Y');
            $table->string('time_format')->default('h:i A');
            $table->string('currency')->default('INR');
            $table->string('currency_symbol')->default("\u{20B9}");
            $table->string('first_day_of_week')->default('monday');
            $table->timestamps();

            $table->unique('school_id');
        });

        $schema->create('security_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->unsignedInteger('password_min_length')->default(8);
            $table->boolean('password_requires_uppercase')->default(true);
            $table->boolean('password_requires_number')->default(true);
            $table->boolean('password_requires_symbol')->default(false);
            $table->unsignedInteger('session_timeout_minutes')->default(120);
            $table->unsignedInteger('max_login_attempts')->default(5);
            $table->unsignedInteger('lockout_minutes')->default(15);
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamps();

            $table->unique('school_id');
        });

        $schema->create('integration_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->enum('integration_type', ['email', 'sms', 'payment', 'storage', 'push', 'maps', 'other']);
            $table->string('provider')->nullable();
            $table->json('config')->nullable();
            $table->longText('encrypted_config')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'integration_type']);
            $table->index(['provider', 'status']);
        });

        $schema->create('setting_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->enum('setting_type', ['setting', 'feature_flag', 'branding', 'localization', 'security', 'integration']);
            $table->string('setting_key')->nullable();
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'setting_type']);
            $table->index(['setting_key', 'changed_by']);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->dropIfExists('setting_audit_logs');
        $schema->dropIfExists('integration_settings');
        $schema->dropIfExists('security_settings');
        $schema->dropIfExists('localization_settings');
        $schema->dropIfExists('branding_settings');
        $schema->dropIfExists('feature_flags');
        $schema->dropIfExists('settings');
        $schema->dropIfExists('setting_groups');
    }
};
