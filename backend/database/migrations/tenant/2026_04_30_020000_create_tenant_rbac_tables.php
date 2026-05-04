<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);
        $db = DB::connection($this->connection);

        $schema->table('permissions', function (Blueprint $table): void {
            if (! Schema::connection('tenant')->hasColumn('permissions', 'action')) {
                $table->string('action')->nullable()->after('module');
            }

            if (! Schema::connection('tenant')->hasColumn('permissions', 'is_system')) {
                $table->boolean('is_system')->default(true)->after('description');
            }

            if (! Schema::connection('tenant')->hasColumn('permissions', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('is_system');
            }

            $table->index(['module', 'action'], 'permissions_module_action_index');
            $table->index('status', 'permissions_status_index');
        });

        $schema->table('roles', function (Blueprint $table): void {
            if (! Schema::connection('tenant')->hasColumn('roles', 'code')) {
                $table->string('code')->nullable()->after('name');
            }

            if (! Schema::connection('tenant')->hasColumn('roles', 'role_type')) {
                $table->enum('role_type', ['system', 'tenant'])->nullable()->after('description');
            }

            if (! Schema::connection('tenant')->hasColumn('roles', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('role_type');
            }

            if (! Schema::connection('tenant')->hasColumn('roles', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('is_default');
            }

            if (! Schema::connection('tenant')->hasColumn('roles', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index('role_type', 'roles_role_type_index');
            $table->index('status', 'roles_status_index');
        });

        $schema->create('role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();

            $table->unique(['role_id', 'permission_id'], 'role_permissions_role_permission_unique');
            $table->index('school_id', 'role_permissions_school_id_index');
            $table->index('role_id', 'role_permissions_role_id_index');
            $table->index('permission_id', 'role_permissions_permission_id_index');
        });

        $schema->create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['user_id', 'role_id', 'school_id'], 'user_roles_user_role_school_unique');
            $table->index('school_id', 'user_roles_school_id_index');
            $table->index('user_id', 'user_roles_user_id_index');
            $table->index('role_id', 'user_roles_role_id_index');
        });

        $schema->create('permission_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('module');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('module', 'permission_groups_module_index');
            $table->index('status', 'permission_groups_status_index');
        });

        $schema->create('role_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();

            $table->index('school_id', 'role_audit_logs_school_id_index');
            $table->index('role_id', 'role_audit_logs_role_id_index');
            $table->index('user_id', 'role_audit_logs_user_id_index');
            $table->index('performed_by', 'role_audit_logs_performed_by_index');
        });

        $db->table('permissions')
            ->select(['id', 'code'])
            ->orderBy('id')
            ->chunkById(100, function ($permissions) use ($db): void {
                foreach ($permissions as $permission) {
                    $segments = array_values(array_filter(explode('.', (string) $permission->code)));
                    $action = count($segments) > 1 ? end($segments) : 'view';

                    $db->table('permissions')
                        ->where('id', $permission->id)
                        ->update([
                            'action' => $action,
                            'is_system' => true,
                            'status' => 'active',
                        ]);
                }
            });

        $db->table('roles')
            ->select(['id', 'school_id', 'slug', 'scope'])
            ->orderBy('id')
            ->chunkById(100, function ($roles) use ($db): void {
                foreach ($roles as $role) {
                    $roleType = ($role->scope === 'system' || $role->school_id === null) ? 'system' : 'tenant';

                    $db->table('roles')
                        ->where('id', $role->id)
                        ->update([
                            'code' => $role->slug,
                            'role_type' => $roleType,
                            'is_default' => false,
                            'status' => 'active',
                        ]);
                }
            });

        if ($schema->hasTable('permission_role')) {
            $db->table('permission_role')
                ->select(['id', 'role_id', 'permission_id', 'created_at', 'updated_at'])
                ->orderBy('id')
                ->chunkById(250, function ($rows) use ($db): void {
                    foreach ($rows as $row) {
                        $roleSchoolId = $db->table('roles')
                            ->where('id', $row->role_id)
                            ->value('school_id');

                        $db->table('role_permissions')->updateOrInsert(
                            [
                                'role_id' => $row->role_id,
                                'permission_id' => $row->permission_id,
                            ],
                            [
                                'school_id' => $roleSchoolId,
                                'created_at' => $row->created_at ?? now(),
                                'updated_at' => $row->updated_at ?? now(),
                            ]
                        );
                    }
                });
        }

        if ($schema->hasTable('role_user')) {
            $db->table('role_user')
                ->select(['id', 'school_id', 'role_id', 'user_id', 'created_at', 'updated_at'])
                ->orderBy('id')
                ->chunkById(250, function ($rows) use ($db): void {
                    foreach ($rows as $row) {
                        $db->table('user_roles')->updateOrInsert(
                            [
                                'school_id' => $row->school_id,
                                'user_id' => $row->user_id,
                                'role_id' => $row->role_id,
                            ],
                            [
                                'assigned_by' => null,
                                'created_at' => $row->created_at ?? now(),
                                'updated_at' => $row->updated_at ?? now(),
                            ]
                        );
                    }
                });
        }

        $db->statement('UPDATE roles SET code = slug WHERE code IS NULL');
        $db->statement("UPDATE roles SET role_type = CASE WHEN (scope = 'system' OR school_id IS NULL) THEN 'system' ELSE 'tenant' END WHERE role_type IS NULL");

        $schema->table('roles', function (Blueprint $table): void {
            $table->string('code')->nullable(false)->change();
            $table->enum('role_type', ['system', 'tenant'])->nullable(false)->change();
            $table->unique(['school_id', 'code'], 'roles_school_id_code_unique');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        $schema->table('roles', function (Blueprint $table): void {
            if (Schema::connection('tenant')->hasColumn('roles', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            $table->dropUnique('roles_school_id_code_unique');
            $table->dropIndex('roles_role_type_index');
            $table->dropIndex('roles_status_index');
            $table->dropColumn(['code', 'role_type', 'is_default', 'status']);
        });

        $schema->table('permissions', function (Blueprint $table): void {
            $table->dropIndex('permissions_module_action_index');
            $table->dropIndex('permissions_status_index');
            $table->dropColumn(['action', 'is_system', 'status']);
        });

        $schema->dropIfExists('role_audit_logs');
        $schema->dropIfExists('permission_groups');
        $schema->dropIfExists('user_roles');
        $schema->dropIfExists('role_permissions');
    }
};
