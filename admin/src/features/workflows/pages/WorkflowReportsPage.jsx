import { Alert, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WorkflowPageShell } from '../components/WorkflowPageShell';
import { WorkflowStatusChip } from '../components/WorkflowStatusChip';
import { fetchWorkflowReports } from '../store/workflowsSlice';

function MetricCard({ label, value, helper }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={1}>
        <Typography variant="overline" color="text.secondary">{label}</Typography>
        <Typography variant="h4">{value}</Typography>
        <Typography variant="body2" color="text.secondary">{helper}</Typography>
      </Stack>
    </Paper>
  );
}

export function WorkflowReportsPage() {
  const dispatch = useAppDispatch();
  const { reports, loading, error } = useAppSelector((state) => state.workflows);

  useEffect(() => {
    dispatch(fetchWorkflowReports());
  }, [dispatch]);

  const workflowSummary = reports.workflowSummary || {};
  const automationSummary = reports.automationSummary || {};
  const approvalPending = reports.approvalPending || {};
  const pendingItems = approvalPending.items?.data || approvalPending.items || [];

  return (
    <WorkflowPageShell
      title="Workflow Reports"
      description="Read workflow volume, approval pressure, and automation execution health from one operational reporting surface."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard label="Workflow Instances" value={workflowSummary.total_instances || 0} helper="Total runtime workflow executions in scope." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard label="Completed Workflows" value={workflowSummary.completed_instances || 0} helper="Workflows that reached the end of their path." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard label="Automation Runs" value={automationSummary.total_runs || 0} helper="Executed automation runs in the current reporting window." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <MetricCard label="Pending Approvals" value={approvalPending.counts?.pending || 0} helper="Requests still waiting on an approver decision." />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Workflow Summary"
            rows={[
              { id: 'pending', label: 'Pending', count: workflowSummary.pending_instances || 0, status: 'pending' },
              { id: 'in_progress', label: 'In Progress', count: workflowSummary.in_progress_instances || 0, status: 'in_progress' },
              { id: 'completed', label: 'Completed', count: workflowSummary.completed_instances || 0, status: 'completed' },
              { id: 'failed', label: 'Failed', count: workflowSummary.failed_instances || 0, status: 'failed' },
            ]}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            columns={[
              { key: 'label', header: 'Bucket' },
              { key: 'count', header: 'Count' },
              { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
            ]}
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Automation Health"
            rows={[
              { id: 'completed', label: 'Completed', count: automationSummary.completed_runs || 0, status: 'completed' },
              { id: 'processing', label: 'Processing', count: automationSummary.processing_runs || 0, status: 'processing' },
              { id: 'failed', label: 'Failed', count: automationSummary.failed_runs || 0, status: 'failed' },
            ]}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            columns={[
              { key: 'label', header: 'Bucket' },
              { key: 'count', header: 'Count' },
              { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
            ]}
          />
        </Grid>
      </Grid>

      <AppDataTable
        title="Pending Approval Snapshot"
        rows={pendingItems}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No pending approvals in the current tenant."
        columns={[
          { key: 'id', header: 'Approval #' },
          { key: 'module', header: 'Module' },
          { key: 'reference_type', header: 'Reference Type' },
          { key: 'reference_id', header: 'Reference ID' },
          { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
        ]}
      />
    </WorkflowPageShell>
  );
}
