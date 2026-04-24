import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchHrOptions, fetchHrResource, setHrResourceFilters } from '../store/hrSlice';

export function LeaveBalancesPage() {
  const dispatch = useAppDispatch();
  const balanceState = useAppSelector((state) => state.hr.resources.leaveBalances);
  const options = useAppSelector((state) => state.hr.options);

  useEffect(() => {
    dispatch(fetchHrOptions());
    dispatch(fetchHrResource({ resource: 'leaveBalances', params: balanceState.filters }));
  }, [dispatch]);

  return (
    <AppDataTable
      title="Leave Balances"
      columns={[
        { key: 'staff', header: 'Staff', render: (row) => row.staff?.full_name || row.staff_id },
        { key: 'leave_type', header: 'Leave Type', render: (row) => row.leave_type?.name || row.leave_type_id },
        { key: 'allocated_days', header: 'Allocated' },
        { key: 'used_days', header: 'Used' },
        { key: 'remaining_days', header: 'Remaining' },
        { key: 'carried_forward_days', header: 'Carry Forward' },
      ]}
      rows={balanceState.items}
      loading={balanceState.loading}
      searchValue=""
      onSearchChange={() => {}}
      filters={[
        {
          key: 'staff_id',
          label: 'Staff',
          value: balanceState.filters.staff_id || '',
          onChange: (value) => {
            dispatch(setHrResourceFilters({ resource: 'leaveBalances', filters: { staff_id: value } }));
            dispatch(fetchHrResource({ resource: 'leaveBalances', params: { ...balanceState.filters, staff_id: value } }));
          },
          options: [{ value: '', label: 'All' }, ...(options.staff || []).map((item) => ({ value: item.id, label: item.full_name }))],
        },
        {
          key: 'leave_type_id',
          label: 'Leave Type',
          value: balanceState.filters.leave_type_id || '',
          onChange: (value) => {
            dispatch(setHrResourceFilters({ resource: 'leaveBalances', filters: { leave_type_id: value } }));
            dispatch(fetchHrResource({ resource: 'leaveBalances', params: { ...balanceState.filters, leave_type_id: value } }));
          },
          options: [{ value: '', label: 'All' }, ...(options.leaveTypes || []).map((item) => ({ value: item.id, label: item.name }))],
        },
      ]}
      emptyState="No leave balances found yet."
    />
  );
}
