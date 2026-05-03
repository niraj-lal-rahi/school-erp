import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import PowerSettingsNewOutlinedIcon from '@mui/icons-material/PowerSettingsNewOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, Grid, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WorkflowPageShell } from '../components/WorkflowPageShell';
import { WorkflowStatusChip } from '../components/WorkflowStatusChip';
import { useWorkflowAccess } from '../hooks/useWorkflowAccess';
import {
  activateWorkflowDefinition,
  createWorkflowDefinition,
  deactivateWorkflowDefinition,
  deleteWorkflowDefinition,
  fetchWorkflowDefinitions,
} from '../store/workflowsSlice';

const initialForm = {
  name: '',
  code: '',
  module: 'general',
  trigger_type: 'manual',
  trigger_event: '',
  description: '',
  status: 'draft',
};

export function WorkflowDefinitionsPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useWorkflowAccess();
  const { definitions, definitionsPagination, loading, saving, error } = useAppSelector((state) => state.workflows);
  const [search, setSearch] = useState('');
  const [module, setModule] = useState('');
  const [status, setStatus] = useState('');
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchWorkflowDefinitions({
      module,
      status,
      per_page: 20,
      page: definitionsPagination.page,
    }));
  }, [definitionsPagination.page, dispatch, module, status]);

  const rows = definitions.filter((item) => {
    if (!search) {
      return true;
    }

    return `${item.name} ${item.code} ${item.module}`.toLowerCase().includes(search.toLowerCase());
  });

  return (
    <WorkflowPageShell
      title="Workflow Definitions"
      description="Define the approval and action blueprints that other modules can start on demand, on schedule, or from domain events."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      {canManage ? (
        <Grid container spacing={3}>
          <Grid size={{ xs: 12 }}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
              <TextField label="Workflow Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} fullWidth />
              <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toUpperCase().replaceAll(' ', '-') }))} fullWidth />
              <TextField select label="Module" value={form.module} onChange={(event) => setForm((current) => ({ ...current, module: event.target.value }))} sx={{ minWidth: 180 }}>
                {['admissions', 'fees', 'attendance', 'hr', 'exams', 'communication', 'transport', 'general'].map((value) => (
                  <MenuItem key={value} value={value}>{value}</MenuItem>
                ))}
              </TextField>
              <TextField select label="Trigger Type" value={form.trigger_type} onChange={(event) => setForm((current) => ({ ...current, trigger_type: event.target.value }))} sx={{ minWidth: 180 }}>
                {['manual', 'event', 'schedule'].map((value) => (
                  <MenuItem key={value} value={value}>{value}</MenuItem>
                ))}
              </TextField>
              <Button
                variant="contained"
                startIcon={<AddOutlinedIcon />}
                disabled={saving || !form.name || !form.code}
                onClick={() => dispatch(createWorkflowDefinition(form)).then((result) => {
                  if (!result.error) {
                    setForm(initialForm);
                  }
                })}
              >
                Create
              </Button>
            </Stack>
          </Grid>
        </Grid>
      ) : null}

      <AppDataTable
        title="Definition Library"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...definitionsPagination,
          onPageChange: (page) => dispatch(fetchWorkflowDefinitions({ module, status, page, per_page: 20 })),
        }}
        filters={[
          {
            key: 'module',
            label: 'Module',
            value: module,
            onChange: setModule,
            options: [
              { label: 'All Modules', value: '' },
              ...['admissions', 'fees', 'attendance', 'hr', 'exams', 'communication', 'transport', 'general'].map((value) => ({ label: value, value })),
            ],
          },
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: setStatus,
            options: [
              { label: 'All Statuses', value: '' },
              ...['active', 'inactive', 'draft'].map((value) => ({ label: value, value })),
            ],
          },
        ]}
        columns={[
          { key: 'name', header: 'Workflow' },
          { key: 'code', header: 'Code' },
          { key: 'module', header: 'Module' },
          { key: 'trigger_type', header: 'Trigger' },
          { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
          { key: 'step_count', header: 'Steps', render: (row) => row.steps?.length ?? row.step_count ?? 0 },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                {canManage ? (
                  <Button
                    size="small"
                    startIcon={<PowerSettingsNewOutlinedIcon />}
                    onClick={() => dispatch(row.status === 'active' ? deactivateWorkflowDefinition(row.id) : activateWorkflowDefinition(row.id))}
                  >
                    {row.status === 'active' ? 'Deactivate' : 'Activate'}
                  </Button>
                ) : null}
                {canManage ? (
                  <Button
                    size="small"
                    color="error"
                    startIcon={<DeleteOutlineOutlinedIcon />}
                    onClick={() => dispatch(deleteWorkflowDefinition(row.id))}
                  >
                    Delete
                  </Button>
                ) : null}
              </Stack>
            ),
          },
        ]}
      />
    </WorkflowPageShell>
  );
}
