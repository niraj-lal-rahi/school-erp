import { Alert, Button, Chip, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';

const initialForm = {
  leave_type_id: '',
  start_date: '',
  end_date: '',
  reason: '',
  status: 'draft',
};

export function StaffLeaveTab({ applications, balances, leaveTypes, saving, error, onCreate }) {
  const [form, setForm] = useState(initialForm);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await onCreate({
      ...form,
      attachment_path: null,
    });

    if (!result?.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, xl: 7 }}>
        <AppDataTable
          title="Leave Applications"
          columns={[
            { key: 'leave_type', header: 'Leave Type', render: (row) => row.leave_type?.name || row.leave_type_id },
            { key: 'period', header: 'Period', render: (row) => `${row.start_date} to ${row.end_date}` },
            { key: 'total_days', header: 'Days' },
            { key: 'status', header: 'Status', render: (row) => <Chip label={row.status} size="small" color={row.status === 'approved' ? 'success' : 'default'} /> },
            { key: 'reason', header: 'Reason' },
          ]}
          rows={applications}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No leave applications on this profile yet."
        />
      </Grid>

      <Grid size={{ xs: 12, xl: 5 }}>
        <Stack spacing={3}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleSubmit}>
              <Typography variant="h6">Create Leave Request</Typography>
              {error ? <Alert severity="error">{error}</Alert> : null}
              <TextField select label="Leave Type" value={form.leave_type_id} onChange={(event) => setForm((current) => ({ ...current, leave_type_id: event.target.value }))}>
                <MenuItem value="">Select</MenuItem>
                {leaveTypes.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>
              <TextField type="date" label="Start Date" value={form.start_date} onChange={(event) => setForm((current) => ({ ...current, start_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
              <TextField type="date" label="End Date" value={form.end_date} onChange={(event) => setForm((current) => ({ ...current, end_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
              <TextField select label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                <MenuItem value="draft">draft</MenuItem>
                <MenuItem value="submitted">submitted</MenuItem>
              </TextField>
              <TextField multiline minRows={3} label="Reason" value={form.reason} onChange={(event) => setForm((current) => ({ ...current, reason: event.target.value }))} />
              <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : 'Create Leave Request'}</Button>
            </Stack>
          </Paper>

          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Leave Balances</Typography>
              {(balances || []).length ? balances.map((item) => (
                <Stack key={item.id} direction="row" justifyContent="space-between">
                  <Typography>{item.leave_type?.name || 'Leave Type'}</Typography>
                  <Typography color="text.secondary">{item.remaining_days} days left</Typography>
                </Stack>
              )) : (
                <Typography color="text.secondary">Leave balances will appear here after approvals.</Typography>
              )}
            </Stack>
          </Paper>
        </Stack>
      </Grid>
    </Grid>
  );
}
