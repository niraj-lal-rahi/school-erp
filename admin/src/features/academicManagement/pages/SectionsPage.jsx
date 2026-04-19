import { AcademicManagementCrudPage } from '../components/AcademicManagementCrudPage';

export function SectionsPage() {
  return (
    <AcademicManagementCrudPage
      resource="sections"
      title="Sections"
      description="Manage class sections and capacities."
      fields={[
        { key: 'school_class_id', label: 'Class', type: 'select', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: `${item.name} (${item.code})` }) },
        { key: 'name', label: 'Name' },
        { key: 'code', label: 'Code' },
        { key: 'capacity', label: 'Capacity', type: 'number' },
        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || row.school_class_id },
        { key: 'capacity', header: 'Capacity' },
        { key: 'status', header: 'Status' },
      ]}
      filters={[
        { key: 'school_class_id', label: 'Class', optionsKey: 'classes', mapOption: (item) => ({ value: item.id, label: item.name }) },
        { key: 'status', label: 'Status', options: [{ value: '', label: 'All' }, { value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
    />
  );
}
