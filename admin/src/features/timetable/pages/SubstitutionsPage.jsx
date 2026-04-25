import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import CloseOutlinedIcon from '@mui/icons-material/CloseOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  approveTimetableSubstitution,
  cancelTimetableSubstitution,
  createTimetableSubstitution,
  deleteTimetableSubstitution,
  fetchTimetableEntries,
  fetchTimetableOptions,
  fetchTimetableSubstitutions,
  updateTimetableSubstitution,
} from '../store/timetableSlice';

const initialForm = {
  id: null,
  timetable_entry_id: '',
  original_staff_id: '',
  substitute_staff_id: '',
  substitution_date: '',
  reason: '',
};

export function SubstitutionsPage() {
  const dispatch = useAppDispatch();
  const { substitutions, entries, options, loading, saving, error } = useAppSelector((state) => state.timetable);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableSubstitutions());
    dispatch(fetchTimetableEntries());
    dispatch(fetchTimetableOptions());
  }, [dispatch]);

  const entryOptions = useMemo(
    () => entries.map((entry) => ({
      value: entry.id,
      label: `${entry.school_class?.name || 'Class'} ${entry.section?.name || ''} - ${entry.day_of_week} - ${entry.period?.name || 'Period'}`,
      originalStaffId: entry.staff_id,
    })),
    [entries],
  );

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();

    return substitutions.filter((item) => {
      const source = [
        item.original_staff?.full_name,
        item.substitute_staff?.full_name,
        item.timetable_entry?.subject?.name,
        item.timetable_entry?.school_class?.name,
        item.reason,
      ].join(' ').toLowerCase();

      const matchesSearch = !query || source.includes(query);
      const matchesStatus = !statusFilter || item.status === statusFilter;

      return matchesSearch && matchesStatus;
    });
  }, [substitutions, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      timetable_entry_id: Number(form.timetable_entry_id),
      original_staff_id: Number(form.original_staff_id),
      substitute_staff_id: Number(form.substitute_staff_id),
      substitution_date: form.substitution_date,
      reason: form.reason || null,
    };

    const action = form.id
      ? updateTimetableSubstitution({ id: form.id, payload })
      : createTimetableSubstitution(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Substitution Management</Typography>
              <Typography variant="body2" color="text.secondary">
                Plan and approve one-off teacher substitutions without changing the published weekly timetable.
              </Typography>
            </Stack>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField
              select
              label="Timetable Entry"
              value={form.timetable_entry_id}
              onChange={(event) => {
                const value = event.target.value;
                const entryOption = entryOptions.find((item) => `${item.value}` === `${value}`);
                setForm((current) => ({
                  ...current,
                  timetable_entry_id: value,
                  original_staff_id: entryOption?.originalStaffId || '',
                }));
              }}
            >
              {entryOptions.map((option) => (
                <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
              ))}
            </TextField>

            <TextField
              select
              label="Original Teacher"
              value={form.original_staff_id}
              onChange={(event) => setForm((current) => ({ ...current, original_staff_id: event.target.value }))}
            >
              {(options.staff || []).map((staff) => (
                <MenuItem key={staff.id} value={staff.id}>{staff.full_name}</MenuItem>
              ))}
            </TextField>

            <TextField
              select
              label="Substitute Teacher"
              value={form.substitute_staff_id}
              onChange={(event) => setForm((current) => ({ ...current, substitute_staff_id: event.target.value }))}
            >
              {(options.staff || []).map((staff) => (
                <MenuItem key={staff.id} value={staff.id}>{staff.full_name}</MenuItem>
              ))}
            </TextField>

            <TextField
              label="Substitution Date"
              type="date"
              value={form.substitution_date}
              onChange={(event) => setForm((current) => ({ ...current, substitution_date: event.target.value }))}
              InputLabelProps={{ shrink: true }}
            />

            <TextField
              label="Reason"
              multiline
              minRows={3}
              value={form.reason}
              onChange={(event) => setForm((current) => ({ ...current, reason: event.target.value }))}
            />

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Substitution' : 'Create Substitution'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Substitution Requests"
          columns={[
            { key: 'date', header: 'Date', render: (row) => row.substitution_date },
            { key: 'class', header: 'Class', render: (row) => `${row.timetable_entry?.school_class?.name || '-'} ${row.timetable_entry?.section?.name || ''}`.trim() },
            { key: 'subject', header: 'Subject', render: (row) => row.timetable_entry?.subject?.name || '-' },
            { key: 'original', header: 'Original Teacher', render: (row) => row.original_staff?.full_name || '-' },
            { key: 'substitute', header: 'Substitute', render: (row) => row.substitute_staff?.full_name || '-' },
            { key: 'status', header: 'Status' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    timetable_entry_id: row.timetable_entry_id || '',
                    original_staff_id: row.original_staff_id || '',
                    substitute_staff_id: row.substitute_staff_id || '',
                    substitution_date: row.substitution_date || '',
                    reason: row.reason || '',
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="success" onClick={() => dispatch(approveTimetableSubstitution(row.id))} disabled={row.status === 'approved'}>
                    <CheckCircleOutlineOutlinedIcon />
                  </IconButton>
                  <IconButton color="warning" onClick={() => dispatch(cancelTimetableSubstitution(row.id))} disabled={row.status === 'cancelled'}>
                    <CloseOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteTimetableSubstitution(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={filteredRows}
          loading={loading}
          searchValue={search}
          onSearchChange={setSearch}
          filters={[
            {
              key: 'status',
              label: 'Status',
              value: statusFilter,
              onChange: setStatusFilter,
              options: [
                { value: '', label: 'All Statuses' },
                { value: 'planned', label: 'Planned' },
                { value: 'approved', label: 'Approved' },
                { value: 'completed', label: 'Completed' },
                { value: 'cancelled', label: 'Cancelled' },
              ],
            },
          ]}
          emptyState="No substitutions have been recorded yet."
        />
      </Grid>
    </Grid>
  );
}
