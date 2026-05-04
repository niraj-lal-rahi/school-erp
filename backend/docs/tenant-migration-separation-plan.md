# Tenant Migration Separation Plan

This document defines the safe migration split for moving school ERP schema into a tenant-only migration path without breaking the current shared-database runtime during transition.

## Target Paths

- Tenant migrations:
  - `database/migrations/tenant`
- Platform migrations:
  - `app/Modules/SuperAdmin/Database/Migrations`
- Shared transitional migrations:
  - `database/migrations`

## Do Not Move Yet

The existing migration files in `database/migrations` should remain in place until:

1. tenant-safe copies exist in `database/migrations/tenant`
2. mixed platform/tenant migrations are split
3. migration commands are updated
4. model connection strategy is finalized

## Tenant-Safe Copy Candidates

These migrations are tenant ERP schema and can be copied into `database/migrations/tenant` with their existing timestamp order preserved.

Status:
- copied into `database/migrations/tenant`:
  - tenant foundation / users
  - SIS
  - academic
  - HR
  - finance
  - attendance
  - timetable
  - transport
  - communication
  - exams
  - reports
  - portal
  - workflows
  - documents
- not copied yet because they require a split pass:
  - none of the tenant runtime migrations remain pending split

### Already Split For Tenant Path

- shared RBAC migration replaced by:
  - `database/migrations/tenant/2026_04_30_020000_create_tenant_rbac_tables.php`
- shared settings migration replaced by:
  - `database/migrations/tenant/2026_05_03_020000_create_tenant_settings_tables.php`
- shared payment migration replaced by:
  - `database/migrations/tenant/2026_05_02_000000_create_tenant_payment_tables.php`
- shared performance index migration replaced by:
  - `database/migrations/tenant/2026_05_03_030000_add_tenant_performance_indexes.php`

### Core tenant auth / school operations

- `0001_01_01_000000_create_users_table.php`
- `2026_04_30_020000_enhance_rbac_tables.php`

### SIS

- `2026_04_19_000100_create_sis_tables.php`
- `2026_04_21_000000_upgrade_sis_student_master_tables.php`
- `2026_04_21_010000_upgrade_admissions_for_workflow.php`
- `2026_04_21_010100_make_admissions_student_id_nullable.php`
- `2026_04_21_020000_upgrade_student_enrollments.php`
- `2026_04_21_030000_upgrade_student_documents_and_create_medical_records.php`
- `2026_04_21_040000_create_student_status_history_and_notes_tables.php`

### Academic

- `2026_04_20_000000_alter_existing_academic_tables_for_academic_management.php`
- `2026_04_20_000100_create_academic_management_tables.php`

### HR

- `2026_04_24_000000_create_hr_staff_foundation_tables.php`
- `2026_04_24_010000_create_hr_staff_profile_tables.php`
- `2026_04_24_020000_create_staff_attendance_table.php`
- `2026_04_24_030000_create_hr_leave_management_tables.php`
- `2026_04_24_040000_create_hr_payroll_tables.php`
- `2026_04_24_050000_create_hr_staff_profile_extension_tables.php`

### Finance

- `2026_04_24_060000_create_finance_fee_foundation_tables.php`
- `2026_04_24_061000_create_finance_fee_structures_and_assignments_tables.php`
- `2026_04_24_062000_create_finance_installments_and_invoices_tables.php`
- `2026_04_24_063000_create_finance_payments_and_receipts_tables.php`
- `2026_04_24_064000_create_finance_discount_fine_and_refund_tables.php`
- `2026_04_24_065000_create_finance_expenses_and_ledger_tables.php`

### Attendance

- `2026_04_25_000000_create_attendance_core_tables.php`
- `2026_04_25_010000_link_staff_attendance_to_status_types.php`
- `2026_04_25_020000_create_attendance_corrections_and_holidays_tables.php`
- `2026_04_25_030000_create_attendance_imports_and_biometric_logs_tables.php`
- `2026_04_25_040000_create_attendance_summary_table.php`

### Timetable

- `2026_04_25_050000_create_timetable_foundation_tables.php`
- `2026_04_26_000000_create_timetable_entries_table.php`
- `2026_04_26_010000_create_timetable_substitutions_and_exceptions_tables.php`

### Transport

- `2026_04_26_020000_create_transport_management_tables.php`

### Communication

- `2026_04_26_030000_create_communication_system_tables.php`

### Exams

- `2026_04_27_000000_create_examination_and_results_tables.php`

### Reports / portal

- `2026_04_30_000000_create_reports_analytics_tables.php`
- `2026_04_30_010000_create_unified_portal_tables.php`

### Payments / workflows / documents / settings

- `2026_05_02_000000_create_payment_gateway_integration_tables.php`
- `2026_05_03_000000_create_workflow_automation_tables.php`
- `2026_05_03_010000_create_document_management_tables.php`
- `2026_05_03_020000_create_system_settings_tables.php`
- `2026_05_03_030000_add_performance_indexes_for_production_hardening.php`

## Platform-Only Migrations

These already belong to the platform path and should stay there:

- `app/Modules/SuperAdmin/Database/Migrations/2026_05_04_010000_create_platform_core_tables.php`
- `app/Modules/SuperAdmin/Database/Migrations/2026_05_04_010100_create_platform_subscription_tables.php`
- `app/Modules/SuperAdmin/Database/Migrations/2026_05_04_010200_create_platform_observability_tables.php`

These shared-database SaaS migrations should eventually be retired or split, not copied into the tenant path:

- `2026_04_30_030000_create_saas_enhancement_tables.php`

## Shared Transitional Migrations

These should remain outside the tenant path for now:

- `0001_01_01_000001_create_cache_table.php`
- `0001_01_01_000002_create_jobs_table.php`

## Migrations That Must Be Split Before True Separation

### RBAC

`2026_04_30_020000_enhance_rbac_tables.php`

Reason:
- mixes global and tenant role semantics
- references `schools`
- upgrades legacy pivot tables with shared-db assumptions

Planned split:
- tenant RBAC schema migration under `database/migrations/tenant`
- platform admin auth/RBAC migration under SuperAdmin module if needed

### Settings

`2026_05_03_020000_create_system_settings_tables.php`

Reason:
- mixes global and tenant rows in the same physical tables using nullable `school_id`
- current `scope` field assumes shared DB fallback

Planned split:
- tenant settings migration under `database/migrations/tenant`
- platform settings migration stays in SuperAdmin module as `platform_settings`

### Payments

`2026_05_02_000000_create_payment_gateway_integration_tables.php`

Reason:
- likely contains platform-linked subscription references
- tenant DB should not depend on central platform billing tables

Planned split:
- tenant payment runtime tables stay tenant-side
- platform billing and subscription linkage stay central

### Performance indexes

`2026_05_03_030000_add_performance_indexes_for_production_hardening.php`

Reason:
- indexes both tenant ERP tables and central/SaaS-style tables

Planned split:
- tenant index migration under `database/migrations/tenant`
- platform index migration under SuperAdmin module if needed

## Dependency Risks

### `schools` coupling

Many tenant migrations reference `schools`. In a database-per-tenant design, those references must either:

- point to a tenant-local school profile table, or
- be rewritten to remove the foreign key dependency

### Shared auth split

`users`, `roles`, `user_roles`, and `role_permissions` are still modeled as shared-db structures. Moving them blindly will break:

- platform login
- super admin role resolution
- existing permission seeders

### Mixed settings/service assumptions

Current service code expects global fallback in the same physical database for some settings and feature-flag reads.

### Payment/platform dependencies

Payment migrations may reference subscription or SaaS tables that belong in the platform DB.

### Seeders

Existing seeders assume shared-db access to:

- `schools`
- tenant users
- RBAC tables
- settings

These will need a separate tenant seeding path.

## Safe Next Steps

1. create tenant-safe copies of the clearly tenant-only migrations into `database/migrations/tenant`
2. create split replacements for:
   - RBAC
   - settings
   - payments
   - performance indexes
3. introduce tenant migration commands using:
   - `php artisan migrate --database=tenant --path=database/migrations/tenant`
4. keep original shared migrations in place until all references are updated
