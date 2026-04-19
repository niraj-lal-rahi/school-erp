import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function LessonPlansPage() {
  return (
    <AcademicManagementCrudPage
      resource="lessonPlans"
      title="Lesson Plans"
      description="Teachers can draft and publish lesson plans for assigned classes, sections, and subjects."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'academic_term_id', label: 'Term', type: 'select', optionsKey: 'terms', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', type: 'select', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'section_id', label: 'Section', type: 'select', optionsKey: 'sections', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', type: 'select', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'staff_id', label: 'Staff', type: 'select', optionsKey: 'staff', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'title', label: 'Title' },
        { key: 'topic', label: 'Topic' },
        { key: 'planned_date', label: 'Planned Date', type: 'date' },
        { key: 'duration_minutes', label: 'Duration Minutes', type: 'number' },
        { key: 'teaching_method', label: 'Teaching Method' },
        { key: 'objectives', label: 'Objectives', type: 'textarea', md: 12 },
        { key: 'materials_needed', label: 'Materials Needed', type: 'textarea', md: 12 },
        { key: 'notes', label: 'Notes', type: 'textarea', md: 12 },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }, { value: 'completed', label: 'Completed' }, { value: 'cancelled', label: 'Cancelled' }] },
      ]}
      columns={[
        { key: 'title', header: 'Title' },
        { key: 'topic', header: 'Topic' },
        { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || row.subject_id },
        { key: 'staff', header: 'Teacher', render: (row) => row.staff?.name || row.staff_id },
        { key: 'planned_date', header: 'Planned Date' },
        { key: 'status', header: 'Status' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'staff_id', label: 'Teacher', optionsKey: 'staff', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'status', label: 'Status', options: [{ value: '', label: 'All' }, { value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }, { value: 'completed', label: 'Completed' }, { value: 'cancelled', label: 'Cancelled' }] },
      ]}
    />
  );
}
