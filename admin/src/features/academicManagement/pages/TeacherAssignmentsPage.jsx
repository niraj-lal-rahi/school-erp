import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function TeacherAssignmentsPage() {
  return (
    <AcademicManagementCrudPage
      resource="teacherAssignments"
      title="Teacher Assignments"
      description="Assign teachers to class, section, and subject combinations."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', type: 'select', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'section_id', label: 'Section', type: 'select', optionsKey: 'sections', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'subject_id', label: 'Subject', type: 'select', optionsKey: 'subjects', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'staff_id', label: 'Staff', type: 'select', optionsKey: 'staff', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'is_class_teacher', label: 'Class Teacher', type: 'checkbox' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || row.school_class_id },
        { key: 'section', header: 'Section', render: (row) => row.section?.name || 'All Sections' },
        { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || row.subject_id },
        { key: 'staff', header: 'Staff', render: (row) => row.staff?.name || row.staff_id },
        { key: 'is_class_teacher', header: 'Class Teacher', render: (row) => row.is_class_teacher ? 'Yes' : 'No' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'school_class_id', label: 'Class', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'staff_id', label: 'Staff', optionsKey: 'staff', mapOption: (item) => ({ value: item.id, label: item.name }) },
      ]}
    />
  );
}
