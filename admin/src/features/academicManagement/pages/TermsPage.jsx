import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function TermsPage() {
  return (
    <AcademicManagementCrudPage
      resource="terms"
      title="Terms / Semesters"
      description="Configure terms within academic years for planning, assignments, and curriculum sequencing."
      fields={[
        { key: 'academic_year_id', label: 'Academic Year', type: 'select', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'start_date', label: 'Start Date', type: 'date' },
        { key: 'end_date', label: 'End Date', type: 'date' },
        { key: 'sequence', label: 'Sequence', type: 'number' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || row.academic_year_id },
        { key: 'sequence', header: 'Sequence' },
        { key: 'status', header: 'Status' },
      ]}
      filters={[
        { key: 'academic_year_id', label: 'Academic Year', optionsKey: 'academicYears', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'status', label: 'Status', options: [{ value: '', label: 'All' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
    />
  );
}
