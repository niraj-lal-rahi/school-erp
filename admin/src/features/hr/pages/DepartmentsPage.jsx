import { HrCrudPage } from '../components/HrCrudPage';

export function DepartmentsPage() {
  return (
    <HrCrudPage
      resource="departments"
      title="Departments"
      description="Maintain the organizational departments that drive staff classification, directory filtering, and payroll reporting."
      fields={[
        { key: 'name', label: 'Name', required: true },
        { key: 'code', label: 'Code', required: true },
        { key: 'description', label: 'Description', type: 'textarea' },
        { key: 'status', label: 'Status', type: 'select', required: true, options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'status', header: 'Status' },
        { key: 'designations_count', header: 'Designations' },
        { key: 'staff_count', header: 'Staff' },
      ]}
      normalizeRecord={(row) => ({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        description: row.description || '',
        status: row.status || 'active',
      })}
      emptyState="No departments have been created yet."
    />
  );
}
