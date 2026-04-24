import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';

const initialForm = {
  attendance_date: '',
  check_in_time: '',
  check_out_time: '',
  attendance_status: 'present',
  source: 'manual',
  remarks: '',
};

export function StaffAttendanceTab({ items, saving, error, onCreate }) {
  const [form, setForm] = useState(initialForm);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await onCreate({
      ...form,
      check_in_time: form.check_in_time || null,
      check_out_time: form.check_out_time || null,
      remarks: form.remarks || null,
    });

    if (!result?.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Attendance"
          columns={[
            { key: 'attendance_date', header: 'Date' },
            { key: 'attendance_status', header: 'Status' },
            { key: 'check_in_time', header: 'Check In' },
            { key: 'check_out_time', header: 'Check Out' },
            { key: 'source', header: 'Source' },
            { key: 'remarks', header: 'Remarks' },
          ]}
          rows={items}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No attendance entries yet."
        />
      </Grid>

      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h6">Mark Attendance</Typography>
            {error ? <Alert severity="error">{error}</Alert> : null}
            <TextField type="date" label="Attendance Date" value={form.attendance_date} onChange={(event) => setForm((current) => ({ ...current, attendance_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
            <TextField type="time" label="Check In" value={form.check_in_time} onChange={(event) => setForm((current) => ({ ...current, check_in_time: event.target.value }))} InputLabelProps={{ shrink: true }} />
            <TextField type="time" label="Check Out" value={form.check_out_time} onChange={(event) => setForm((current) => ({ ...current, check_out_time: event.target.value }))} InputLabelProps={{ shrink: true }} />
            <TextField select label="Status" value={form.attendance_status} onChange={(event) => setForm((current) => ({ ...current, attendance_status: event.target.value }))}>
              {['present', 'absent', 'half_day', 'late', 'leave', 'holiday'].map((status) => (
                <MenuItem key={status} value={status}>{status}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Source" value={form.source} onChange={(event) => setForm((current) => ({ ...current, source: event.target.value }))}>
              {['manual', 'biometric', 'import', 'mobile'].map((source) => (
                <MenuItem key={source} value={source}>{source}</MenuItem>
              ))}
            </TextField>
            <TextField multiline minRows={3} label="Remarks" value={form.remarks} onChange={(event) => setForm((current) => ({ ...current, remarks: event.target.value }))} />
            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : 'Save Attendance'}
            </Button>
          </Stack>
        </Paper>
      </Grid>
    </Grid>
  );
}
