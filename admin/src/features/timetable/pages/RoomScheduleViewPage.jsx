import { MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { TimetablePrintButton } from '../components/TimetablePrintButton';
import { WeeklyTimetableGrid } from '../components/WeeklyTimetableGrid';
import { fetchTimetableEntries, fetchTimetableOptions } from '../store/timetableSlice';

export function RoomScheduleViewPage() {
  const dispatch = useAppDispatch();
  const { options, entries, loading } = useAppSelector((state) => state.timetable);
  const [roomId, setRoomId] = useState('');
  const [versionId, setVersionId] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableOptions());
  }, [dispatch]);

  useEffect(() => {
    if (roomId) {
      dispatch(fetchTimetableEntries({
        room_id: roomId,
        ...(versionId ? { timetable_version_id: versionId } : {}),
      }));
    }
  }, [dispatch, roomId, versionId]);

  const selectedRoom = useMemo(
    () => (options.rooms || []).find((item) => `${item.id}` === `${roomId}`),
    [options.rooms, roomId],
  );

  const subtitle = selectedRoom
    ? `Weekly occupancy for ${selectedRoom.name}${selectedRoom.building ? `, ${selectedRoom.building}` : ''}`
    : 'Select a room to review its weekly booking pattern.';

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} justifyContent="space-between" alignItems={{ xs: 'stretch', md: 'center' }}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Room Schedule View</Typography>
              <Typography variant="body2" color="text.secondary">
                Track classroom, lab, and shared-space utilization before publishing the timetable.
              </Typography>
            </Stack>
            <TimetablePrintButton label="Print room schedule" />
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField select label="Room" value={roomId} onChange={(event) => setRoomId(event.target.value)}>
              {(options.rooms || []).map((item) => (
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
        title={loading ? 'Loading room timetable...' : 'Weekly Room Schedule'}
        subtitle={subtitle}
        periods={options.periods || []}
        entries={entries}
      />
    </Stack>
  );
}
