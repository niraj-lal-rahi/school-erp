import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchHrOptions, fetchHrResource, setHrResourceFilters } from '../store/hrSlice';

export function PayslipsPage() {
  const dispatch = useAppDispatch();
  const payslipState = useAppSelector((state) => state.hr.resources.payslips);
  const options = useAppSelector((state) => state.hr.options);

  useEffect(() => {
    dispatch(fetchHrOptions());
    dispatch(fetchHrResource({ resource: 'payslips', params: payslipState.filters }));
  }, [dispatch]);

  return (
    <AppDataTable
      title="Payslips"
      columns={[
        { key: 'staff', header: 'Staff', render: (row) => row.staff?.full_name || row.staff_id },
        { key: 'gross_salary', header: 'Gross Salary' },
        { key: 'total_deductions', header: 'Deductions' },
        { key: 'net_salary', header: 'Net Salary' },
        { key: 'payment_status', header: 'Payment Status' },
        { key: 'paid_at', header: 'Paid At' },
      ]}
      rows={payslipState.items}
      loading={payslipState.loading}
      searchValue=""
      onSearchChange={() => {}}
      filters={[
        {
          key: 'staff_id',
          label: 'Staff',
          value: payslipState.filters.staff_id || '',
          onChange: (value) => {
            dispatch(setHrResourceFilters({ resource: 'payslips', filters: { staff_id: value } }));
            dispatch(fetchHrResource({ resource: 'payslips', params: { ...payslipState.filters, staff_id: value } }));
          },
          options: [{ value: '', label: 'All' }, ...(options.staff || []).map((item) => ({ value: item.id, label: item.full_name }))],
        },
        {
          key: 'payment_status',
          label: 'Payment Status',
          value: payslipState.filters.payment_status || '',
          onChange: (value) => {
            dispatch(setHrResourceFilters({ resource: 'payslips', filters: { payment_status: value } }));
            dispatch(fetchHrResource({ resource: 'payslips', params: { ...payslipState.filters, payment_status: value } }));
          },
          options: [{ value: '', label: 'All' }, ...['unpaid', 'paid', 'failed'].map((item) => ({ value: item, label: item }))],
        },
      ]}
      emptyState="No payslips generated yet."
    />
  );
}
