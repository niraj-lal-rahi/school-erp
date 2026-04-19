import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function AcademicYearsPage() {
  return (
    <AcademicManagementCrudPage
      resource="academicYears"
      title="Academic Years"
      description="Manage academic sessions and control the single active year for the current tenant."
      allowAcademicYearActivation
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'start_date', label: 'Start Date', type: 'date' },
        { key: 'end_date', label: 'End Date', type: 'date' },
        { key: 'is_active', label: 'Is Active', type: 'checkbox', md: 3 },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'draft', label: 'Draft' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'archived', label: 'Archived' }], md: 3 },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'dates', header: 'Dates', render: (row) => `${row.start_date} to ${row.end_date}` },
        { key: 'status', header: 'Status' },
        { key: 'is_active', header: 'Active', render: (row) => row.is_active ? 'Yes' : 'No' },
      ]}
      filters={[
        { key: 'status', label: 'Status', options: [{ value: '', label: 'All' }, { value: 'draft', label: 'Draft' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }, { value: 'archived', label: 'Archived' }] },
      ]}
    />
  );
}
