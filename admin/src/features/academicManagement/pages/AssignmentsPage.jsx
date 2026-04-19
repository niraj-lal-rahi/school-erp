import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function AssignmentsPage() {
  return (
    <AcademicManagementCrudPage
      resource="assignments"
      title="Assignments / Homework"
      description="Create and manage assignment work for class, section, subject, and teacher combinations."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'academic_term_id', label: 'Term', type: 'select', optionsKey: 'terms', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', type: 'select', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'section_id', label: 'Section', type: 'select', optionsKey: 'sections', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', type: 'select', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'staff_id', label: 'Teacher', type: 'select', optionsKey: 'staff', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'title', label: 'Title' },
        { key: 'assigned_date', label: 'Assigned Date', type: 'date' },
        { key: 'due_date', label: 'Due Date', type: 'date' },
        { key: 'total_marks', label: 'Total Marks', type: 'number' },
        { key: 'attachment_path', label: 'Attachment Path' },
        { key: 'description', label: 'Description', type: 'textarea', md: 12 },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'draft', label: 'Draft' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'title', header: 'Title' },
        { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || row.subject_id },
        { key: 'staff', header: 'Teacher', render: (row) => row.staff?.name || row.staff_id },
        { key: 'assigned_date', header: 'Assigned' },
        { key: 'due_date', header: 'Due' },
        { key: 'status', header: 'Status' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'staff_id', label: 'Teacher', optionsKey: 'staff', mapOption: (item) => ({ value: item.id, label: item.name }) },
      ]}
    />
  );
}
