import PauseCircleOutlineOutlinedIcon from '@mui/icons-material/PauseCircleOutlineOutlined';
import PlayCircleOutlineOutlinedIcon from '@mui/icons-material/PlayCircleOutlineOutlined';
import ScheduleOutlinedIcon from '@mui/icons-material/ScheduleOutlined';
import { Alert, Button, Grid, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ReportsFormDrawer } from '../components/ReportsFormDrawer';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { useReportsAccess } from '../hooks/useReportsAccess';
import { createReportSchedule, fetchReportDefinitions, fetchReportSchedules, pauseReportSchedule, resumeReportSchedule } from '../store/reportsSlice';
import { deliveryChannelOptions, scheduleStatusOptions, scheduleTypeOptions } from '../types/options';

const initialForm = {
  report_definition_id: '',
  schedule_type: 'daily',
  channel: 'in_app',
  time: '08:00',
  day_of_week: 1,
  day_of_month: 1,
  email: '',
};

export function ScheduleReportsPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useReportsAccess();
  const { schedules, schedulesPagination, definitions, loading, saving, error } = useAppSelector((state) => state.reports);
  const [filters, setFilters] = useState({ status: '' });
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchReportDefinitions({ per_page: 100 }));
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchReportSchedules({
      status: filters.status || undefined,
      per_page: 20,
      page: schedulesPagination.page,
    }));
  }, [dispatch, filters.status, schedulesPagination.page]);

  async function handleCreateSchedule(event) {
    event.preventDefault();

    const schedule_config = {
      time: form.time,
      ...(form.schedule_type === 'once' ? { run_at: new Date().toISOString() } : {}),
      ...(form.schedule_type === 'weekly' ? { day_of_week: Number(form.day_of_week) } : {}),
      ...(form.schedule_type === 'monthly' ? { day_of_month: Number(form.day_of_month) } : {}),
    };

    const payload = {
      report_definition_id: Number(form.report_definition_id),
      schedule_type: form.schedule_type,
      schedule_config,
      next_run_at: new Date().toISOString(),
      channel: form.channel,
      recipients: form.email
        ? [{ email: form.email }]
        : [{ user_type: 'admin', user_id: 1 }],
      status: 'active',
    };

    await dispatch(createReportSchedule(payload));
    setDrawerOpen(false);
    setForm(initialForm);
  }

  return (
    <ReportsPageShell
      title="Schedule Reports"
      description="Automate recurring report delivery so stakeholders get the right summaries without manual follow-through."
      actions={canManage ? (
        <Button variant="contained" startIcon={<ScheduleOutlinedIcon />} onClick={() => setDrawerOpen(true)}>
          New Schedule
        </Button>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Report Schedules"
        columns={[
          { key: 'report_definition', header: 'Report', render: (row) => row.report_definition?.name || 'N/A' },
          { key: 'schedule_type', header: 'Type' },
          { key: 'channel', header: 'Channel' },
          { key: 'next_run_at', header: 'Next Run', render: (row) => row.next_run_at ? new Date(row.next_run_at).toLocaleString() : 'N/A' },
          { key: 'last_run_at', header: 'Last Run', render: (row) => row.last_run_at ? new Date(row.last_run_at).toLocaleString() : 'N/A' },
          { key: 'status', header: 'Status' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => canManage ? (
              row.status === 'active' ? (
                <Button size="small" startIcon={<PauseCircleOutlineOutlinedIcon />} onClick={() => dispatch(pauseReportSchedule(row.id))}>
                  Pause
                </Button>
              ) : (
                <Button size="small" startIcon={<PlayCircleOutlineOutlinedIcon />} onClick={() => dispatch(resumeReportSchedule({ id: row.id, payload: {} }))}>
                  Resume
                </Button>
              )
            ) : 'View only',
          },
        ]}
        rows={schedules}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: filters.status,
            onChange: (value) => setFilters({ status: value }),
            options: scheduleStatusOptions,
          },
        ]}
        pagination={{
          ...schedulesPagination,
          onPageChange: (page) => dispatch(fetchReportSchedules({
            status: filters.status || undefined,
            per_page: 20,
            page,
          })),
        }}
        emptyState="No report schedules available."
      />

      <ReportsFormDrawer
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        title="Create Report Schedule"
        subtitle="This basic builder keeps the scheduling inputs deliberate and quick."
      >
        <Stack component="form" spacing={2} onSubmit={handleCreateSchedule}>
          <TextField select fullWidth label="Report Definition" value={form.report_definition_id} onChange={(event) => setForm((current) => ({ ...current, report_definition_id: event.target.value }))} required>
            <MenuItem value="">Select</MenuItem>
            {definitions.map((item) => (
              <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
            ))}
          </TextField>
          <Grid container spacing={2}>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField select fullWidth label="Schedule Type" value={form.schedule_type} onChange={(event) => setForm((current) => ({ ...current, schedule_type: event.target.value }))}>
                {scheduleTypeOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField select fullWidth label="Channel" value={form.channel} onChange={(event) => setForm((current) => ({ ...current, channel: event.target.value }))}>
                {deliveryChannelOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid size={{ xs: 12, md: 6 }}>
              <TextField fullWidth label="Time" type="time" InputLabelProps={{ shrink: true }} value={form.time} onChange={(event) => setForm((current) => ({ ...current, time: event.target.value }))} />
            </Grid>
            {form.schedule_type === 'weekly' ? (
              <Grid size={{ xs: 12, md: 6 }}>
                <TextField select fullWidth label="Day Of Week" value={form.day_of_week} onChange={(event) => setForm((current) => ({ ...current, day_of_week: event.target.value }))}>
                  <MenuItem value={0}>Sunday</MenuItem>
                  <MenuItem value={1}>Monday</MenuItem>
                  <MenuItem value={2}>Tuesday</MenuItem>
                  <MenuItem value={3}>Wednesday</MenuItem>
                  <MenuItem value={4}>Thursday</MenuItem>
                  <MenuItem value={5}>Friday</MenuItem>
                  <MenuItem value={6}>Saturday</MenuItem>
                </TextField>
              </Grid>
            ) : null}
            {form.schedule_type === 'monthly' ? (
              <Grid size={{ xs: 12, md: 6 }}>
                <TextField fullWidth label="Day Of Month" type="number" value={form.day_of_month} onChange={(event) => setForm((current) => ({ ...current, day_of_month: event.target.value }))} />
              </Grid>
            ) : null}
            <Grid size={{ xs: 12 }}>
              <TextField fullWidth label="Recipient Email (optional)" value={form.email} onChange={(event) => setForm((current) => ({ ...current, email: event.target.value }))} />
            </Grid>
          </Grid>
          <Stack direction="row" justifyContent="flex-end" spacing={1.5}>
            <Button variant="text" onClick={() => setDrawerOpen(false)}>Cancel</Button>
            <Button type="submit" variant="contained" disabled={saving || !form.report_definition_id}>
              {saving ? 'Saving...' : 'Create Schedule'}
            </Button>
          </Stack>
        </Stack>
      </ReportsFormDrawer>
    </ReportsPageShell>
  );
}
