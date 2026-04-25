import { Alert, Button, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { checkTimetableConflicts, clearTimetableConflictCheck, fetchTimetableOptions } from '../store/timetableSlice';

const initialForm = {
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
};

export function ConflictCheckerPage() {
  const dispatch = useAppDispatch();
  const { options, conflictCheck, saving, error } = useAppSelector((state) => state.timetable);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchTimetableOptions());

    return () => {
      dispatch(clearTimetableConflictCheck());
    };
  }, [dispatch]);

  const sectionsForClass = useMemo(
    () => (options.sections || []).filter((section) => !form.school_class_id || `${section.school_class_id}` === `${form.school_class_id}`),
    [options.sections, form.school_class_id],
  );

  function handleSubmit(event) {
    event.preventDefault();

    dispatch(checkTimetableConflicts({
      ...form,
      timetable_version_id: Number(form.timetable_version_id),
      academic_year_id: Number(form.academic_year_id),
      school_class_id: Number(form.school_class_id),
      section_id: Number(form.section_id),
      attendance_period_id: Number(form.attendance_period_id),
      subject_id: form.subject_id ? Number(form.subject_id) : null,
      staff_id: form.staff_id ? Number(form.staff_id) : null,
      room_id: form.room_id ? Number(form.room_id) : null,
    }));
  }

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack component="form" spacing={2} onSubmit={handleSubmit}>
        <Typography variant="h5">Conflict Checker</Typography>
        <Typography variant="body2" color="text.secondary">
          Validate a proposed timetable slot before saving it, especially when teachers and rooms are tightly shared.
        </Typography>

        {error ? <Alert severity="error">{error}</Alert> : null}
        {conflictCheck.conflicts.length ? (
          <Alert severity="warning">
            {conflictCheck.conflicts.map((conflict) => conflict.message).join(' ')}
          </Alert>
        ) : conflictCheck.hasConflicts === false && (conflictCheck.conflicts || []).length === 0 ? null : (
          <Alert severity="success">No conflicts found for this timetable slot.</Alert>
        )}

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
          {['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'].map((day) => (
            <MenuItem key={day} value={day}>{day.charAt(0).toUpperCase() + day.slice(1)}</MenuItem>
          ))}
        </TextField>
        <TextField select label="Period" value={form.attendance_period_id} onChange={(event) => setForm((current) => ({ ...current, attendance_period_id: event.target.value }))}>
          {(options.periods || []).map((item) => (
            <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
          ))}
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

        <Button type="submit" variant="contained" disabled={saving}>
          {saving ? 'Checking...' : 'Run Conflict Check'}
        </Button>
      </Stack>
    </Paper>
  );
}
