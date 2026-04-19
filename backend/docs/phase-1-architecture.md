# School ERP SaaS Phase 1

This phase implements the platform foundations for:

- Multi-tenant architecture
- JWT authentication with RBAC
- Student Information System (SIS)

## Tenant Strategy

- Shared database, row-level isolation through `school_id`
- `TenantContext` resolves the active school from `X-Tenant-Code`, `X-Tenant-Id`, `X-Tenant-Domain`, or the authenticated user
- Tenant-aware models apply a global scope automatically
- Platform-level roles can exist with `school_id = null`

## Database Schema

### Platform / Multi-tenant

#### `schools`
- `id`
- `uuid`
- `name`
- `code`
- `slug`
- `domain`
- `timezone`
- `locale`
- `status`
- `settings`
- `storage_disk`
- timestamps

#### `academic_years`
- `id`
- `school_id` -> `schools.id`
- `uuid`
- `name`
- `start_date`
- `end_date`
- `is_current`
- timestamps

### Authentication / RBAC

#### `users`
- `id`
- `uuid`
- `school_id` -> `schools.id` nullable for platform users
- `first_name`
- `last_name`
- `name`
- `email`
- `phone`
- `password`
- `status`
- `last_login_at`
- `email_verified_at`
- `remember_token`
- timestamps

Unique constraints:
- `school_id + email`
- `school_id + phone`

#### `roles`
- `id`
- `school_id` -> `schools.id` nullable
- `uuid`
- `name`
- `slug`
- `scope`
- `description`
- timestamps

Unique constraints:
- `school_id + slug`

#### `permissions`
- `id`
- `uuid`
- `name`
- `code`
- `module`
- `description`
- timestamps

Unique constraints:
- `code`

#### `role_user`
- `id`
- `school_id` -> `schools.id`
- `role_id` -> `roles.id`
- `user_id` -> `users.id`
- timestamps

Unique constraints:
- `school_id + role_id + user_id`

#### `permission_role`
- `id`
- `role_id` -> `roles.id`
- `permission_id` -> `permissions.id`
- timestamps

Unique constraints:
- `role_id + permission_id`

#### `refresh_tokens`
- `id`
- `user_id` -> `users.id`
- `school_id` -> `schools.id` nullable
- `token_id`
- `token_hash`
- `expires_at`
- `last_used_at`
- `revoked_at`
- timestamps

Unique constraints:
- `token_id`

### SIS

#### `guardians`
- `id`
- `school_id` -> `schools.id`
- `uuid`
- `first_name`
- `last_name`
- `email`
- `phone`
- `relationship_type`
- `occupation`
- `address`
- timestamps

#### `school_classes`
- `id`
- `school_id` -> `schools.id`
- `academic_year_id` -> `academic_years.id`
- `uuid`
- `name`
- `code`
- `grade_level`
- `sort_order`
- timestamps

#### `sections`
- `id`
- `school_id` -> `schools.id`
- `school_class_id` -> `school_classes.id`
- `uuid`
- `name`
- `capacity`
- `class_teacher_id` -> `users.id` nullable
- timestamps

#### `students`
- `id`
- `school_id` -> `schools.id`
- `uuid`
- `user_id` -> `users.id` nullable
- `admission_no`
- `first_name`
- `last_name`
- `preferred_name`
- `email`
- `phone`
- `gender`
- `date_of_birth`
- `admission_date`
- `blood_group`
- `status`
- `photo_path`
- `address`
- `medical_notes`
- timestamps

Unique constraints:
- `school_id + admission_no`
- `school_id + email`

#### `student_guardian`
- `id`
- `school_id` -> `schools.id`
- `student_id` -> `students.id`
- `guardian_id` -> `guardians.id`
- `relationship`
- `is_primary`
- `is_emergency_contact`
- `pickup_authorized`
- timestamps

Unique constraints:
- `school_id + student_id + guardian_id`

#### `admissions`
- `id`
- `school_id` -> `schools.id`
- `student_id` -> `students.id`
- `academic_year_id` -> `academic_years.id`
- `applied_class_id` -> `school_classes.id`
- `status`
- `applied_on`
- `admitted_on`
- `remarks`
- timestamps

#### `student_enrollments`
- `id`
- `school_id` -> `schools.id`
- `student_id` -> `students.id`
- `academic_year_id` -> `academic_years.id`
- `school_class_id` -> `school_classes.id`
- `section_id` -> `sections.id` nullable
- `roll_number`
- `status`
- `joined_on`
- `ended_on`
- timestamps

#### `student_documents`
- `id`
- `school_id` -> `schools.id`
- `student_id` -> `students.id`
- `uploaded_by` -> `users.id` nullable
- `document_type`
- `title`
- `disk`
- `file_path`
- `metadata`
- timestamps

## Relationship Summary

- School has many academic years, users, roles, guardians, classes, sections, students, admissions, enrollments, documents
- User belongs to school and has many roles
- Role belongs to school and belongs to many permissions/users
- Student belongs to school, optionally belongs to a portal user, belongs to many guardians, has many admissions, enrollments, and documents
- Guardian belongs to school and belongs to many students
- Enrollment belongs to student, school, academic year, class, and optionally section
