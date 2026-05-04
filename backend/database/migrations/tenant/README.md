# Tenant Migration Path

This directory contains tenant ERP migrations intended to run on the `tenant` database connection.

Run with:

```powershell
php artisan migrate --database=tenant --path=database/migrations/tenant
```

## Included

The current copy set includes tenant-safe migrations for:

- tenant users and school foundation
- SIS
- academic management
- HR
- finance
- attendance
- timetable
- transport
- communication
- examinations
- reports
- portal
- workflows
- documents

## Intentionally Excluded For Now

These migrations remain outside this path until they are split for database-per-tenant compatibility:

- `2026_04_30_030000_create_saas_enhancement_tables.php`

The shared RBAC migration has now been replaced here by:

- `2026_04_30_020000_create_tenant_rbac_tables.php`

The shared settings migration has now been replaced here by:

- `2026_05_03_020000_create_tenant_settings_tables.php`

The shared payment migration has now been replaced here by:

- `2026_05_02_000000_create_tenant_payment_tables.php`

The shared performance index migration has now been replaced here by:

- `2026_05_03_030000_add_tenant_performance_indexes.php`

## Important

These files are currently copies of the shared migration source. Do not delete the original migration files until the application runtime, seeders, and deployment commands are fully switched to the separated platform + tenant architecture.
