import { AppDataTable } from '../../../components/common/AppDataTable';

export function StaffStatusHistoryTab({ items }) {
  return (
    <AppDataTable
      title="Status History"
      columns={[
        { key: 'effective_date', header: 'Effective Date' },
        { key: 'action_type', header: 'Action' },
        { key: 'previous_status', header: 'Previous Status' },
        { key: 'new_status', header: 'New Status' },
        { key: 'reason', header: 'Reason' },
        { key: 'performer', header: 'Performed By', render: (row) => row.performer?.name || row.performed_by || 'System' },
      ]}
      rows={items}
      loading={false}
      searchValue=""
      onSearchChange={() => {}}
      emptyState="No lifecycle history found."
    />
  );
}
