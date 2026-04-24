import { HrCrudPage } from '../components/HrCrudPage';

export function LeaveTypesPage() {
  return (
    <HrCrudPage
      resource="leaveTypes"
      title="Leave Types"
      description="Manage paid and unpaid leave policies, annual quotas, and carry-forward behavior."
      fields={[
        { key: 'name', label: 'Name', required: true },
        { key: 'code', label: 'Code', required: true },
        { key: 'annual_quota', label: 'Annual Quota' },
        { key: 'carry_forward_allowed', label: 'Carry Forward Allowed', type: 'select', required: true, options: [{ value: true, label: 'Yes' }, { value: false, label: 'No' }] },
        { key: 'paid_leave', label: 'Paid Leave', type: 'select', required: true, options: [{ value: true, label: 'Yes' }, { value: false, label: 'No' }] },
        { key: 'status', label: 'Status', type: 'select', required: true, options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
      ]}
      columns={[
        { key: 'name', header: 'Name' },
        { key: 'code', header: 'Code' },
        { key: 'annual_quota', header: 'Quota' },
        { key: 'carry_forward_allowed', header: 'Carry Forward', render: (row) => row.carry_forward_allowed ? 'Yes' : 'No' },
        { key: 'paid_leave', header: 'Paid', render: (row) => row.paid_leave ? 'Yes' : 'No' },
        { key: 'status', header: 'Status' },
      ]}
      normalizeRecord={(row) => ({
        id: row.id,
        name: row.name || '',
        code: row.code || '',
        annual_quota: row.annual_quota || '',
        carry_forward_allowed: Boolean(row.carry_forward_allowed),
        paid_leave: Boolean(row.paid_leave),
        status: row.status || 'active',
      })}
      transformBeforeSubmit={(values) => ({
        ...values,
        annual_quota: values.annual_quota === '' ? null : Number(values.annual_quota),
        carry_forward_allowed: values.carry_forward_allowed === true || values.carry_forward_allowed === 'true',
        paid_leave: values.paid_leave === true || values.paid_leave === 'true',
      })}
      emptyState="No leave types created yet."
    />
  );
}
