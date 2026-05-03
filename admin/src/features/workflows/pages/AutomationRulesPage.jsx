import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import FlashOnOutlinedIcon from '@mui/icons-material/FlashOnOutlined';
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
  activateAutomationRule,
  createAutomationRule,
  deactivateAutomationRule,
  deleteAutomationRule,
  fetchAutomationRules,
  runAutomationRule,
} from '../store/workflowsSlice';

const initialForm = {
  name: '',
  code: '',
  module: 'general',
  trigger_type: 'condition',
  trigger_event: '',
  schedule_expression: '',
  conditions: '{"field":"status","operator":"=","value":"overdue"}',
  actions: '[{"type":"in_app","title":"Workflow automation","message":"Automation fired."}]',
  status: 'draft',
};

export function AutomationRulesPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useWorkflowAccess();
  const { automations, automationsPagination, loading, saving, error } = useAppSelector((state) => state.workflows);
  const [search, setSearch] = useState('');
  const [module, setModule] = useState('');
  const [status, setStatus] = useState('');
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchAutomationRules({ module, status, per_page: 20, page: automationsPagination.page }));
  }, [automationsPagination.page, dispatch, module, status]);

  const rows = automations.filter((item) => `${item.name} ${item.code} ${item.module}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <WorkflowPageShell
      title="Automation Rules"
      description="Create event, schedule, and condition-based automations that handle reminders, alerts, notifications, and follow-up actions."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      {canManage ? (
        <Grid container spacing={2}>
          <Grid size={{ xs: 12 }}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
              <TextField label="Rule Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} fullWidth />
              <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toUpperCase().replaceAll(' ', '-') }))} fullWidth />
              <TextField select label="Module" value={form.module} onChange={(event) => setForm((current) => ({ ...current, module: event.target.value }))} sx={{ minWidth: 180 }}>
                {['fees', 'attendance', 'exams', 'communication', 'transport', 'hr', 'general'].map((value) => (
                  <MenuItem key={value} value={value}>{value}</MenuItem>
                ))}
              </TextField>
              <Button
                variant="contained"
                startIcon={<AddOutlinedIcon />}
                disabled={saving || !form.name || !form.code}
                onClick={() => dispatch(createAutomationRule({
                  ...form,
                  conditions: JSON.parse(form.conditions || '{}'),
                  actions: JSON.parse(form.actions || '[]'),
                })).then((result) => {
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
        title="Automation Catalog"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...automationsPagination,
          onPageChange: (page) => dispatch(fetchAutomationRules({ module, status, page, per_page: 20 })),
        }}
        filters={[
          {
            key: 'module',
            label: 'Module',
            value: module,
            onChange: setModule,
            options: [{ label: 'All Modules', value: '' }, ...['fees', 'attendance', 'exams', 'communication', 'transport', 'hr', 'general'].map((value) => ({ label: value, value }))],
          },
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: setStatus,
            options: [{ label: 'All Statuses', value: '' }, ...['active', 'inactive', 'draft'].map((value) => ({ label: value, value }))],
          },
        ]}
        columns={[
          { key: 'name', header: 'Rule' },
          { key: 'code', header: 'Code' },
          { key: 'module', header: 'Module' },
          { key: 'trigger_type', header: 'Trigger Type' },
          { key: 'last_run_at', header: 'Last Run', render: (row) => row.last_run_at || 'Never' },
          { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              canManage ? (
                <Stack direction="row" spacing={1}>
                  <Button
                    size="small"
                    startIcon={<FlashOnOutlinedIcon />}
                    onClick={() => dispatch(runAutomationRule({ id: row.id, payload: { metadata: { source: 'manual_ui' } } }))}
                  >
                    Run
                  </Button>
                  <Button
                    size="small"
                    startIcon={<PowerSettingsNewOutlinedIcon />}
                    onClick={() => dispatch(row.status === 'active' ? deactivateAutomationRule(row.id) : activateAutomationRule(row.id))}
                  >
                    {row.status === 'active' ? 'Deactivate' : 'Activate'}
                  </Button>
                  <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deleteAutomationRule(row.id))}>
                    Delete
                  </Button>
                </Stack>
              ) : null
            ),
          },
        ]}
      />
    </WorkflowPageShell>
  );
}
