# Academic Management Module

The Academic Management module uses the existing tenant column `school_id` as the tenant isolation key. This project already treats `school_id` as the active tenant id, so the new module stays consistent with the established multi-tenant architecture.

## Core Tables

### Existing Tables Extended

#### `academic_years`
- `id`
- `school_id`
- `uuid`
- `name`
- `code`
- `start_date`
- `end_date`
- `is_active`
- `is_current`
- `status`
- `created_by`
- `updated_by`
- timestamps
- soft deletes

Rules:
- only one active academic year per tenant
- unique `code` per tenant

#### `school_classes`
- `id`
- `school_id`
- `academic_year_id`
- `uuid`
- `name`
- `code`
- `grade_level`
- `level_order`
- `sort_order`
- `description`
- `status`
- timestamps
- soft deletes

Rules:
- unique `code` per tenant and academic year

#### `sections`
- `id`
- `school_id`
- `school_class_id`
- `uuid`
- `name`
- `code`
- `capacity`
- `class_teacher_id`
- `status`
- timestamps
- soft deletes

Rules:
- unique `code` per tenant and class

## New Tables

### `academic_terms`
- `id`
- `school_id`
- `academic_year_id`
- `uuid`
- `name`
- `code`
- `start_date`
- `end_date`
- `sequence`
- `status`
- timestamps
- soft deletes

### `subjects`
- `id`
- `school_id`
- `uuid`
- `name`
- `code`
- `type`
- `description`
- `status`
- timestamps
- soft deletes

### `class_subject_assignments`
- `id`
- `school_id`
- `academic_year_id`
- `school_class_id`
- `section_id`
- `subject_id`
- `is_optional`
- `weekly_periods`
- `status`
- timestamps
- soft deletes

### `teacher_assignments`
- `id`
- `school_id`
- `academic_year_id`
- `school_class_id`
- `section_id`
- `subject_id`
- `staff_id`
- `is_class_teacher`
- `status`
- timestamps
- soft deletes

### `curricula`
- `id`
- `school_id`
- `academic_year_id`
- `school_class_id`
- `subject_id`
- `academic_term_id`
- `title`
- `description`
- `sequence`
- `learning_outcomes`
- `status`
- timestamps
- soft deletes

### `lesson_plans`
- `id`
- `school_id`
- `academic_year_id`
- `academic_term_id`
- `school_class_id`
- `section_id`
- `subject_id`
- `staff_id`
- `title`
- `topic`
- `objectives`
- `teaching_method`
- `planned_date`
- `duration_minutes`
- `materials_needed`
- `notes`
- `status`
- timestamps
- soft deletes

### `homework_assignments`
- `id`
- `school_id`
- `academic_year_id`
- `academic_term_id`
- `school_class_id`
- `section_id`
- `subject_id`
- `staff_id`
- `title`
- `description`
- `assigned_date`
- `due_date`
- `total_marks`
- `attachment_path`
- `status`
- timestamps
- soft deletes

### `academic_calendar_events`
- `id`
- `school_id`
- `academic_year_id`
- `title`
- `description`
- `event_type`
- `start_datetime`
- `end_datetime`
- `is_holiday`
- `audience_type`
- `school_class_id`
- `section_id`
- `status`
- timestamps
- soft deletes

### `grading_structures`
- `id`
- `school_id`
- `academic_year_id`
- `name`
- `description`
- `pass_percentage`
- `status`
- timestamps
- soft deletes

### `grade_scale_items`
- `id`
- `grading_structure_id`
- `grade_label`
- `min_percentage`
- `max_percentage`
- `grade_point`
- `remarks`
- timestamps

## Relationship Summary

- Academic year has many terms, classes, class-subject assignments, teacher assignments, curricula, lesson plans, homework assignments, calendar events, grading structures
- Class belongs to academic year and has many sections, class-subject assignments, teacher assignments, curricula, lesson plans, homework assignments, calendar events
- Section belongs to class and has many class-subject assignments, teacher assignments, lesson plans, homework assignments, calendar events
- Subject has many class-subject assignments, teacher assignments, curricula, lesson plans, homework assignments
- Teacher assignment links staff, class, section, subject, and academic year
- Curriculum links academic year, class, subject, and optional term
- Lesson plan and homework assignment both link academic year, class, section, subject, optional term, and staff
- Grading structure has many grade scale items

## API Namespace

All module APIs live under:

- `/api/v1/academic-management/academic-years`
- `/api/v1/academic-management/terms`
- `/api/v1/academic-management/classes`
- `/api/v1/academic-management/sections`
- `/api/v1/academic-management/subjects`
- `/api/v1/academic-management/class-subjects`
- `/api/v1/academic-management/teacher-assignments`
- `/api/v1/academic-management/curriculum`
- `/api/v1/academic-management/lesson-plans`
- `/api/v1/academic-management/assignments`
- `/api/v1/academic-management/academic-calendar`
- `/api/v1/academic-management/grading-structures`
