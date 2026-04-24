import { HrCrudPage } from '../components/HrCrudPage';

export function SalaryComponentsPage() {
  return (
    <HrCrudPage
      resource="salaryComponents"
      title="Salary Components"
      description="Define reusable earnings and deductions before attaching them to staff salary structures."
      fields={[
        { key: 'name', label: 'Name', required: true },
        { key: 'code', label: 'Code', required: true },
        { key: 'component_type', label: 'Component Type', type: 'select', required: true, options: [{ value: 'earning', label: 'Earning' }, { value: 'deduction', label: 'Deduction' }] },
        { key: 'calculation_type', label: 'Calculation Type', type: 'select', required: true, options: [{ value: 'fixed', label: 'Fixed' }, { value: 'percentage', label: 'Percentage' }] },
        { key: 'default_value', label: 'Default Value' },
        { key: 'taxable', label: 'Taxable', type: 'select', required: true, options: [{ value: true, label: 'Yes' }, { value: false, label: 'No' }] },
        { key: 'status', label: 'Status', type: 'select', required: true, options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'component_type', header: 'Type' },
        { key: 'calculation_type', header: 'Calculation' },
        { key: 'default_value', header: 'Default Value' },
        { key: 'taxable', header: 'Taxable', render: (row) => row.taxable ? 'Yes' : 'No' },
        { key: 'status', header: 'Status' },
      ]}
      normalizeRecord={(row) => ({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        component_type: row.component_type || 'earning',
        calculation_type: row.calculation_type || 'fixed',
        default_value: row.default_value || '',
        taxable: Boolean(row.taxable),
        status: row.status || 'active',
      })}
      transformBeforeSubmit={(values) => ({
        ...values,
        default_value: values.default_value === '' ? null : Number(values.default_value),
        taxable: values.taxable === true || values.taxable === 'true',
      })}
      emptyState="No salary components defined yet."
    />
  );
}
