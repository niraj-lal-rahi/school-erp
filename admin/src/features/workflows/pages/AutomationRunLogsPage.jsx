import { Alert } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WorkflowPageShell } from '../components/WorkflowPageShell';
import { WorkflowStatusChip } from '../components/WorkflowStatusChip';
import { fetchAutomationRuns } from '../store/workflowsSlice';

export function AutomationRunLogsPage() {
  const dispatch = useAppDispatch();
  const { automationRuns, automationRunsPagination, loading, error } = useAppSelector((state) => state.workflows);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  useEffect(() => {
    dispatch(fetchAutomationRuns({ status, per_page: 20, page: automationRunsPagination.page }));
  }, [automationRunsPagination.page, dispatch, status]);

  const rows = automationRuns.filter((item) => `${item.id} ${item.automation_rule_id} ${item.error_message || ''}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <WorkflowPageShell
      title="Automation Run Logs"
      description="Trace each automation execution, spot failing rules early, and understand the throughput of your scheduled and event-driven jobs."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Run History"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...automationRunsPagination,
          onPageChange: (page) => dispatch(fetchAutomationRuns({ status, page, per_page: 20 })),
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: setStatus,
            options: [{ label: 'All Statuses', value: '' }, ...['pending', 'processing', 'completed', 'failed'].map((value) => ({ label: value, value }))],
          },
        ]}
        columns={[
          { key: 'id', header: 'Run #' },
          { key: 'automation_rule_id', header: 'Rule ID' },
          { key: 'records_processed', header: 'Processed' },
          { key: 'success_count', header: 'Success' },
          { key: 'failed_count', header: 'Failed' },
          { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
          { key: 'started_at', header: 'Started At', render: (row) => row.started_at || 'N/A' },
          { key: 'error_message', header: 'Error', render: (row) => row.error_message || 'None' },
        ]}
      />
    </WorkflowPageShell>
  );
}
