import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function SubjectsPage() {
  return (
    <AcademicManagementCrudPage
      resource="subjects"
      title="Subjects"
      description="Manage the subject master used across classes, curriculum, and teacher assignments."
      fields={[
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'type', label: 'Type', type: 'select', options: [{ value: 'mandatory', label: 'Mandatory' }, { value: 'elective', label: 'Elective' }, { value: 'practical', label: 'Practical' }] },
        { key: 'description', label: 'Description', type: 'textarea', md: 12 },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'type', header: 'Type' },
        { key: 'status', header: 'Status' },
      ]}
      filters={[
        { key: 'type', label: 'Type', options: [{ value: '', label: 'All' }, { value: 'mandatory', label: 'Mandatory' }, { value: 'elective', label: 'Elective' }, { value: 'practical', label: 'Practical' }] },
        { key: 'status', label: 'Status', options: [{ value: '', label: 'All' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
    />
  );
}
