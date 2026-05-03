import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import NotificationsActiveOutlinedIcon from '@mui/icons-material/NotificationsActiveOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, Grid, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WorkflowPageShell } from '../components/WorkflowPageShell';
import { WorkflowStatusChip } from '../components/WorkflowStatusChip';
import { useWorkflowAccess } from '../hooks/useWorkflowAccess';
import {
  createReminderRule,
  deleteReminderRule,
  fetchReminderRules,
  processDueReminderRules,
} from '../store/workflowsSlice';

const initialForm = {
  name: '',
  code: '',
  module: 'fees',
  reminder_type: 'after_due',
  offset_days: 1,
  frequency: 'daily',
  channel: 'multi',
  status: 'active',
};

export function ReminderRulesPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useWorkflowAccess();
  const { reminders, remindersPagination, reminderProcessingResult, loading, saving, error } = useAppSelector((state) => state.workflows);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchReminderRules({ status, per_page: 20, page: remindersPagination.page }));
  }, [dispatch, remindersPagination.page, status]);

  const rows = reminders.filter((item) => `${item.name} ${item.code} ${item.module}`.toLowerCase().includes(search.toLowerCase()));

  return (
    <WorkflowPageShell
      title="Reminder Rules"
      description="Configure recurring and due-based reminders for fees, attendance, examinations, transport, and staff follow-up flows."
      actions={canManage ? (
        <Button
          variant="outlined"
          startIcon={<NotificationsActiveOutlinedIcon />}
          disabled={saving}
          onClick={() => dispatch(processDueReminderRules())}
        >
          Process Due
        </Button>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {reminderProcessingResult ? <Alert severity="success">Processed {reminderProcessingResult.processed || 0} due reminder candidates.</Alert> : null}

      {canManage ? (
        <Grid container spacing={2}>
          <Grid size={{ xs: 12 }}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
              <TextField label="Reminder Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} fullWidth />
              <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toUpperCase().replaceAll(' ', '-') }))} fullWidth />
              <TextField select label="Module" value={form.module} onChange={(event) => setForm((current) => ({ ...current, module: event.target.value }))} sx={{ minWidth: 180 }}>
                {['fees', 'attendance', 'exams', 'transport', 'hr', 'general'].map((value) => (
                  <MenuItem key={value} value={value}>{value}</MenuItem>
                ))}
              </TextField>
              <Button
                variant="contained"
                startIcon={<AddOutlinedIcon />}
                disabled={saving || !form.name || !form.code}
                onClick={() => dispatch(createReminderRule(form)).then((result) => {
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
        title="Reminder Rules"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...remindersPagination,
          onPageChange: (page) => dispatch(fetchReminderRules({ status, page, per_page: 20 })),
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: status,
            onChange: setStatus,
            options: [{ label: 'All Statuses', value: '' }, ...['active', 'inactive'].map((value) => ({ label: value, value }))],
          },
        ]}
        columns={[
          { key: 'name', header: 'Rule' },
          { key: 'code', header: 'Code' },
          { key: 'module', header: 'Module' },
          { key: 'reminder_type', header: 'Type' },
          { key: 'channel', header: 'Channel' },
          { key: 'status', header: 'Status', render: (row) => <WorkflowStatusChip value={row.status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              canManage ? (
                <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deleteReminderRule(row.id))}>
                  Delete
                </Button>
              ) : null
            ),
          },
        ]}
      />
    </WorkflowPageShell>
  );
}
