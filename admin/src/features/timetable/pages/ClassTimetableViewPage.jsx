import { MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { TimetablePrintButton } from '../components/TimetablePrintButton';
import { WeeklyTimetableGrid } from '../components/WeeklyTimetableGrid';
import { fetchClassWeeklyTimetable, fetchTimetableOptions } from '../store/timetableSlice';

export function ClassTimetableViewPage() {
  const dispatch = useAppDispatch();
  const { options, classWeekly, loading } = useAppSelector((state) => state.timetable);
  const [classId, setClassId] = useState('');
  const [sectionId, setSectionId] = useState('');
  const [versionId, setVersionId] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableOptions());
  }, [dispatch]);

  useEffect(() => {
    if (classId && sectionId) {
      dispatch(fetchClassWeeklyTimetable({
        classId,
        sectionId,
        params: versionId ? { timetable_version_id: versionId } : {},
      }));
    }
  }, [dispatch, classId, sectionId, versionId]);

  const sectionsForClass = useMemo(
    () => (options.sections || []).filter((section) => !classId || `${section.school_class_id}` === `${classId}`),
    [options.sections, classId],
  );

  const selectedClass = useMemo(
    () => (options.classes || []).find((item) => `${item.id}` === `${classId}`),
    [options.classes, classId],
  );

  const selectedSection = useMemo(
    () => (sectionsForClass || []).find((item) => `${item.id}` === `${sectionId}`),
    [sectionsForClass, sectionId],
  );

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} justifyContent="space-between" alignItems={{ xs: 'stretch', md: 'center' }}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Class Timetable View</Typography>
              <Typography variant="body2" color="text.secondary">
                Review the weekly schedule for a specific class and section using the same draft or published version teachers will follow.
              </Typography>
            </Stack>
            <TimetablePrintButton label="Print class timetable" />
          </Stack>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField select label="Class" value={classId} onChange={(event) => { setClassId(event.target.value); setSectionId(''); }}>
              {(options.classes || []).map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Section" value={sectionId} onChange={(event) => setSectionId(event.target.value)}>
              {sectionsForClass.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
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
        title={loading ? 'Loading class timetable...' : 'Weekly Class Timetable'}
        subtitle={selectedClass ? `${selectedClass.name}${selectedSection ? ` / ${selectedSection.name}` : ''}` : ''}
        periods={options.periods || []}
        entries={classWeekly}
      />
    </Stack>
  );
}
