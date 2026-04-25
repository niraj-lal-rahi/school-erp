import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import RuleOutlinedIcon from '@mui/icons-material/RuleOutlined';
import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { WeeklyTimetableGrid } from '../components/WeeklyTimetableGrid';
import {
  checkTimetableConflicts,
  clearTimetableConflictCheck,
  createTimetableEntry,
  deleteTimetableEntry,
  fetchTimetableEntries,
  fetchTimetableOptions,
  updateTimetableEntry,
} from '../store/timetableSlice';

const dayOptions = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

const initialForm = {
  id: null,
  timetable_version_id: '',
  academic_year_id: '',
  school_class_id: '',
  section_id: '',
  day_of_week: 'monday',
  attendance_period_id: '',
  subject_id: '',
  staff_id: '',
  room_id: '',
  entry_type: 'class',
  notes: '',
  status: 'active',
};

export function WeeklyTimetableBuilderPage() {
  const dispatch = useAppDispatch();
  const { entries, options, loading, saving, error, conflictCheck } = useAppSelector((state) => state.timetable);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableOptions());
    dispatch(fetchTimetableEntries());

    return () => {
      dispatch(clearTimetableConflictCheck());
    };
  }, [dispatch]);

  const sectionsForClass = useMemo(
    () => (options.sections || []).filter((section) => !form.school_class_id || `${section.school_class_id}` === `${form.school_class_id}`),
    [options.sections, form.school_class_id],
  );

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();
    if (!query) return entries;

    return entries.filter((item) => `${item.day_of_week} ${item.subject?.name || ''} ${item.staff?.full_name || ''} ${item.room?.name || ''}`.toLowerCase().includes(query));
  }, [entries, search]);

  const previewEntries = useMemo(() => (
    entries.filter((item) => {
      if (form.timetable_version_id && `${item.timetable_version_id}` !== `${form.timetable_version_id}`) {
        return false;
      }

      if (form.school_class_id && `${item.school_class_id}` !== `${form.school_class_id}`) {
        return false;
      }

      if (form.section_id && `${item.section_id}` !== `${form.section_id}`) {
        return false;
      }

      return true;
    })
  ), [entries, form.timetable_version_id, form.school_class_id, form.section_id]);

  const selectedClass = useMemo(
    () => (options.classes || []).find((item) => `${item.id}` === `${form.school_class_id}`),
    [options.classes, form.school_class_id],
  );

  const selectedSection = useMemo(
    () => sectionsForClass.find((item) => `${item.id}` === `${form.section_id}`),
    [sectionsForClass, form.section_id],
  );

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      ...form,
      timetable_version_id: Number(form.timetable_version_id),
      academic_year_id: Number(form.academic_year_id),
      school_class_id: Number(form.school_class_id),
      section_id: Number(form.section_id),
      attendance_period_id: Number(form.attendance_period_id),
      subject_id: form.subject_id ? Number(form.subject_id) : null,
      staff_id: form.staff_id ? Number(form.staff_id) : null,
      room_id: form.room_id ? Number(form.room_id) : null,
    };

    const action = form.id
      ? updateTimetableEntry({ id: form.id, payload: { ...payload, id: undefined } })
      : createTimetableEntry(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(clearTimetableConflictCheck());
    }
  }

  function handleConflictCheck() {
    const payload = {
      ...form,
      timetable_version_id: Number(form.timetable_version_id),
      academic_year_id: Number(form.academic_year_id),
      school_class_id: Number(form.school_class_id),
      section_id: Number(form.section_id),
      attendance_period_id: Number(form.attendance_period_id),
      subject_id: form.subject_id ? Number(form.subject_id) : null,
      staff_id: form.staff_id ? Number(form.staff_id) : null,
      room_id: form.room_id ? Number(form.room_id) : null,
      ignore_entry_id: form.id || null,
    };

    dispatch(checkTimetableConflicts(payload));
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Weekly Timetable Builder</Typography>
              <Typography variant="body2" color="text.secondary">
                Build weekly slots with an immediate visual preview before publishing a version to the school.
              </Typography>
            </Stack>

            {error ? <Alert severity="error">{error}</Alert> : null}
            {conflictCheck.hasConflicts ? (
              <Alert severity="warning">
                {conflictCheck.conflicts.map((conflict) => conflict.message).join(' ')}
              </Alert>
            ) : null}

            <TextField select label="Version" value={form.timetable_version_id} onChange={(event) => setForm((current) => ({ ...current, timetable_version_id: event.target.value }))}>
              {(options.versions || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Academic Year" value={form.academic_year_id} onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}>
              {(options.academicYears || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Class" value={form.school_class_id} onChange={(event) => setForm((current) => ({ ...current, school_class_id: event.target.value, section_id: '' }))}>
              {(options.classes || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Section" value={form.section_id} onChange={(event) => setForm((current) => ({ ...current, section_id: event.target.value }))}>
              {sectionsForClass.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Day" value={form.day_of_week} onChange={(event) => setForm((current) => ({ ...current, day_of_week: event.target.value }))}>
              {dayOptions.map((day) => (
                <MenuItem key={day} value={day}>{day.charAt(0).toUpperCase() + day.slice(1)}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Period" value={form.attendance_period_id} onChange={(event) => setForm((current) => ({ ...current, attendance_period_id: event.target.value }))}>
              {(options.periods || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Entry Type" value={form.entry_type} onChange={(event) => setForm((current) => ({ ...current, entry_type: event.target.value }))}>
              <MenuItem value="class">Class</MenuItem>
              <MenuItem value="break">Break</MenuItem>
              <MenuItem value="activity">Activity</MenuItem>
              <MenuItem value="free">Free</MenuItem>
            </TextField>
            <TextField select label="Subject" value={form.subject_id} onChange={(event) => setForm((current) => ({ ...current, subject_id: event.target.value }))}>
              <MenuItem value="">None</MenuItem>
              {(options.subjects || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Teacher" value={form.staff_id} onChange={(event) => setForm((current) => ({ ...current, staff_id: event.target.value }))}>
              <MenuItem value="">None</MenuItem>
              {(options.staff || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Room" value={form.room_id} onChange={(event) => setForm((current) => ({ ...current, room_id: event.target.value }))}>
              <MenuItem value="">None</MenuItem>
              {(options.rooms || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField label="Notes" multiline minRows={3} value={form.notes} onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))} />

            <Stack direction="row" spacing={2}>
              <Button type="submit" variant="contained" disabled={saving}>
                {saving ? 'Saving...' : form.id ? 'Update Entry' : 'Create Entry'}
              </Button>
              <Button type="button" variant="outlined" startIcon={<RuleOutlinedIcon />} onClick={handleConflictCheck}>
                Check Conflicts
              </Button>
            </Stack>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <Stack spacing={3}>
          <WeeklyTimetableGrid
            title="Selection Preview"
            subtitle={selectedClass ? `${selectedClass.name}${selectedSection ? ` / ${selectedSection.name}` : ''}` : 'Choose a class and section to preview the weekly layout while you build.'}
            periods={options.periods || []}
            entries={previewEntries}
          />

          <AppDataTable
            title="Timetable Entries"
            columns={[
              { key: 'day_of_week', header: 'Day' },
              { key: 'period', header: 'Period', render: (row) => row.period?.name || '-' },
              { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || row.entry_type },
              { key: 'staff', header: 'Teacher', render: (row) => row.staff?.full_name || '-' },
              { key: 'room', header: 'Room', render: (row) => row.room?.name || '-' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <Stack direction="row" spacing={1}>
                    <IconButton color="primary" onClick={() => setForm({
                      id: row.id,
                      timetable_version_id: row.timetable_version_id || '',
                      academic_year_id: row.academic_year_id || '',
                      school_class_id: row.school_class_id || '',
                      section_id: row.section_id || '',
                      day_of_week: row.day_of_week || 'monday',
                      attendance_period_id: row.attendance_period_id || '',
                      subject_id: row.subject_id || '',
                      staff_id: row.staff_id || '',
                      room_id: row.room_id || '',
                      entry_type: row.entry_type || 'class',
                      notes: row.notes || '',
                      status: row.status || 'active',
                    })}>
                      <EditOutlinedIcon />
                    </IconButton>
                    <IconButton color="secondary" onClick={() => {
                      setForm((current) => ({
                        ...current,
                        timetable_version_id: row.timetable_version_id || '',
                        academic_year_id: row.academic_year_id || '',
                        school_class_id: row.school_class_id || '',
                        section_id: row.section_id || '',
                      }));
                    }}>
                      <VisibilityOutlinedIcon />
                    </IconButton>
                    <IconButton color="error" onClick={() => dispatch(deleteTimetableEntry(row.id))}>
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
            emptyState="No timetable entries created yet."
          />
        </Stack>
      </Grid>
    </Grid>
  );
}
