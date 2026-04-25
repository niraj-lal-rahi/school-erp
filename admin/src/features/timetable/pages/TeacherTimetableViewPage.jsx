import { MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { TimetablePrintButton } from '../components/TimetablePrintButton';
import { WeeklyTimetableGrid } from '../components/WeeklyTimetableGrid';
import { fetchStaffWeeklyTimetable, fetchTimetableOptions } from '../store/timetableSlice';

export function TeacherTimetableViewPage() {
  const dispatch = useAppDispatch();
  const { options, staffWeekly, loading } = useAppSelector((state) => state.timetable);
  const [staffId, setStaffId] = useState('');
  const [versionId, setVersionId] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableOptions());
  }, [dispatch]);

  useEffect(() => {
    if (staffId) {
      dispatch(fetchStaffWeeklyTimetable({
        staffId,
        params: versionId ? { timetable_version_id: versionId } : {},
      }));
    }
  }, [dispatch, staffId, versionId]);

  const selectedStaff = useMemo(
    () => (options.staff || []).find((item) => `${item.id}` === `${staffId}`),
    [options.staff, staffId],
  );

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} justifyContent="space-between" alignItems={{ xs: 'stretch', md: 'center' }}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Teacher Timetable View</Typography>
              <Typography variant="body2" color="text.secondary">
                Review the weekly teaching load for a selected staff member and spot availability gaps before substitutions are added.
              </Typography>
            </Stack>
            <TimetablePrintButton label="Print teacher timetable" />
          </Stack>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField select label="Teacher" value={staffId} onChange={(event) => setStaffId(event.target.value)}>
              {(options.staff || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Version" value={versionId} onChange={(event) => setVersionId(event.target.value)}>
              <MenuItem value="">Latest available</MenuItem>
              {(options.versions || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
          </Stack>
        </Stack>
      </Paper>

      <WeeklyTimetableGrid
        title={loading ? 'Loading teacher timetable...' : 'Weekly Teacher Timetable'}
        subtitle={selectedStaff ? selectedStaff.full_name : ''}
        periods={options.periods || []}
        entries={staffWeekly}
      />
    </Stack>
  );
}
