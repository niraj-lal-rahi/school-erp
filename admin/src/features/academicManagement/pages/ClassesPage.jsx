import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function ClassesPage() {
  return (
    <AcademicManagementCrudPage
      resource="classes"
      title="Classes / Grades"
      description="Define class levels for each academic year."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'grade_level', label: 'Grade Level', type: 'number' },
        { key: 'level_order', label: 'Level Order', type: 'number' },
        { key: 'description', label: 'Description', type: 'textarea', md: 12 },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || row.academic_year_id },
        { key: 'grade_level', header: 'Grade Level' },
        { key: 'status', header: 'Status' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'status', label: 'Status', options: [{ value: '', label: 'All' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
    />
  );
}
