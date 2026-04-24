import { HrCrudPage } from '../components/HrCrudPage';

export function DesignationsPage() {
  return (
    <HrCrudPage
      resource="designations"
      title="Designations"
      description="Keep titles like Principal, Teacher, Librarian, and Accountant structured and searchable for staffing and reporting."
      fields={[
        {
          key: 'department_id',
          label: 'Department',
          type: 'select',
          options: (options) => [{ value: '', label: 'Select' }, ...(options.departments || []).map((item) => ({ value: item.id, label: item.name }))],
        },
        { key: 'name', label: 'Name', required: true },
        { key: 'code', label: 'Code', required: true },
        { key: 'description', label: 'Description', type: 'textarea' },
        { key: 'status', label: 'Status', type: 'select', required: true, options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'department', header: 'Department', render: (row) => row.department?.name || 'Unassigned' },
        { key: 'status', header: 'Status' },
        { key: 'staff_count', header: 'Staff Count' },
      ]}
      filters={[
        {
          key: 'department_id',
          label: 'Department',
          options: (options) => [{ value: '', label: 'All' }, ...(options.departments || []).map((item) => ({ value: item.id, label: item.name }))],
        },
      ]}
      normalizeRecord={(row) => ({
        id: row.id,
        department_id: row.department?.id || '',
        name: row.name || '',
        code: row.code || '',
        description: row.description || '',
        status: row.status || 'active',
      })}
      emptyState="No designations have been created yet."
    />
  );
}
