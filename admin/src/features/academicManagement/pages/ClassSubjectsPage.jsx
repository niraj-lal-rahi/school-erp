import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function ClassSubjectsPage() {
  return (
    <AcademicManagementCrudPage
      resource="classSubjects"
      title="Class Subject Assignments"
      description="Assign subjects to a class or a class-section for the selected academic year."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', type: 'select', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'section_id', label: 'Section', type: 'select', optionsKey: 'sections', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', type: 'select', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'is_optional', label: 'Optional Subject', type: 'checkbox' },
        { key: 'weekly_periods', label: 'Weekly Periods', type: 'number' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || row.academic_year_id },
        { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || row.school_class_id },
        { key: 'section', header: 'Section', render: (row) => row.section?.name || 'All Sections' },
        { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || row.subject_id },
        { key: 'weekly_periods', header: 'Weekly Periods' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
      ]}
    />
  );
}
