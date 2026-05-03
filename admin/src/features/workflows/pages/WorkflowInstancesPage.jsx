import StopCircleOutlinedIcon from '@mui/icons-material/StopCircleOutlined';
import PlayCircleOutlineOutlinedIcon from '@mui/icons-material/PlayCircleOutlineOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WorkflowPageShell } from '../components/WorkflowPageShell';
import { WorkflowStatusChip } from '../components/WorkflowStatusChip';
import { useWorkflowAccess } from '../hooks/useWorkflowAccess';
import {
  cancelWorkflowInstance,
  fetchWorkflowDefinitions,
  fetchWorkflowInstances,
  startWorkflow,
} from '../store/workflowsSlice';

export function WorkflowInstancesPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useWorkflowAccess();
  const { definitions, instances, instancesPagination, lastStartedWorkflow, loading, saving, error } = useAppSelector((state) => state.workflows);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [startPayload, setStartPayload] = useState({
    workflow_definition_id: '',
    reference_type: 'student',
    reference_id: '1',
    metadata: '{"source":"admin_console"}',
  });

  useEffect(() => {
    dispatch(fetchWorkflowDefinitions({ per_page: 100 }));
    dispatch(fetchWorkflowInstances({ per_page: 20, status }));
  }, [dispatch, status]);

  const rows = instances.filter((item) => `${item.id} ${item.reference_type} ${item.reference_id}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <WorkflowPageShell
      title="Workflow Instances"
      description="Monitor every live workflow run, start manual instances, and stop paths that need human intervention."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {lastStartedWorkflow ? <Alert severity="success">Workflow instance #{lastStartedWorkflow.id} started successfully.</Alert> : null}

      {canManage ? (
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack spacing={2}>
            <Typography variant="h6">Start Manual Workflow</Typography>
            <Grid container spacing={2}>
              <Grid size={{ xs: 12, md: 4 }}>
                <TextField
                  select
                  label="Workflow Definition"
                  value={startPayload.workflow_definition_id}
                  onChange={(event) => setStartPayload((current) => ({ ...current, workflow_definition_id: event.target.value }))}
                  fullWidth
                >
                  {definitions.map((definition) => (
                    <MenuItem key={definition.id} value={definition.id}>{definition.name}</MenuItem>
                  ))}
                </TextField>
              </Grid>
              <Grid size={{ xs: 12, md: 3 }}>
                <TextField label="Reference Type" value={startPayload.reference_type} onChange={(event) => setStartPayload((current) => ({ ...current, reference_type: event.target.value }))} fullWidth />
              </Grid>
              <Grid size={{ xs: 12, md: 3 }}>
                <TextField label="Reference ID" value={startPayload.reference_id} onChange={(event) => setStartPayload((current) => ({ ...current, reference_id: event.target.value }))} fullWidth />
              </Grid>
              <Grid size={{ xs: 12, md: 2 }}>
                <Button
                  fullWidth
                  variant="contained"
                  startIcon={<PlayCircleOutlineOutlinedIcon />}
                  disabled={saving || !startPayload.workflow_definition_id}
                  onClick={() => dispatch(startWorkflow({
                    workflow_definition_id: Number(startPayload.workflow_definition_id),
                    reference_type: startPayload.reference_type,
                    reference_id: Number(startPayload.reference_id),
                    metadata: JSON.parse(startPayload.metadata || '{}'),
                  }))}
                >
                  Start
                </Button>
              </Grid>
            </Grid>
          </Stack>
        </Paper>
      ) : null}

      <AppDataTable
        title="Runtime Instances"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...instancesPagination,
          onPageChange: (page) => dispatch(fetchWorkflowInstances({ status, page, per_page: 20 })),
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: setStatus,
            options: [
              { label: 'All Statuses', value: '' },
              ...['pending', 'in_progress', 'approved', 'rejected', 'completed', 'cancelled', 'failed'].map((value) => ({ label: value, value })),
            ],
          },
        ]}
        columns={[
          { key: 'id', header: 'Instance #' },
          { key: 'workflow_definition_id', header: 'Definition ID' },
          { key: 'reference_type', header: 'Reference Type' },
          { key: 'reference_id', header: 'Reference ID' },
          { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
          { key: 'started_at', header: 'Started', render: (row) => row.started_at || 'Not started' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              canManage ? (
                <Button
                  size="small"
                  color="error"
                  startIcon={<StopCircleOutlinedIcon />}
                  disabled={saving || ['completed', 'cancelled', 'failed'].includes(row.status)}
                  onClick={() => dispatch(cancelWorkflowInstance({ id: row.id, payload: { remarks: 'Cancelled from workflow runtime board.' } }))}
                >
                  Cancel
                </Button>
              ) : null
            ),
          },
        ]}
      />
    </WorkflowPageShell>
  );
}
