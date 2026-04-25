import { Alert, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { AttendanceBulkMarkingCard } from '../components/AttendanceBulkMarkingCard';
import { AttendancePageShell } from '../components/AttendancePageShell';
import {
  bulkMarkStudentSession,
  fetchAttendanceOptions,
  fetchSessionStudents,
  fetchStudentSessionById,
  fetchStudentSessions,
  lockStudentSession,
  submitStudentSession,
} from '../store/attendanceSlice';

export function AttendanceBulkMarkingPage() {
  const dispatch = useAppDispatch();
  const { currentSession, studentSessions, sessionStudents, options, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [searchParams, setSearchParams] = useSearchParams();
  const [sessionId, setSessionId] = useState(searchParams.get('sessionId') || '');
  const [recordMap, setRecordMap] = useState({});

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
    dispatch(fetchStudentSessions({}));
  }, [dispatch]);

  useEffect(() => {
    if (!sessionId) {
      return;
    }

    dispatch(fetchStudentSessionById(sessionId));
  }, [dispatch, sessionId]);

  useEffect(() => {
    if (!currentSession?.id) {
      return;
    }

    dispatch(fetchSessionStudents({
      class_id: currentSession.school_class_id,
      section_id: currentSession.section_id,
    }));

    const nextMap = (currentSession.records || []).reduce((carry, record) => ({
      ...carry,
      [record.student_id]: {
        attendance_status_type_id: record.attendance_status_type_id || '',
        remarks: record.remarks || '',
      },
    }), {});

    setRecordMap(nextMap);
  }, [currentSession, dispatch]);

  const availableSessions = useMemo(() => studentSessions.slice().sort((a, b) => String(b.attendance_date).localeCompare(String(a.attendance_date))), [studentSessions]);

  function handleRecordChange(studentId, key, value) {
    setRecordMap((current) => ({
      ...current,
      [studentId]: {
        ...(current[studentId] || {}),
        [key]: value,
      },
    }));
  }

  async function handleSave() {
    if (!currentSession?.id) {
      return;
    }

    const records = Object.entries(recordMap)
      .filter(([, record]) => record.attendance_status_type_id)
      .map(([studentId, record]) => ({
        student_id: Number(studentId),
        attendance_status_type_id: Number(record.attendance_status_type_id),
        remarks: record.remarks || undefined,
      }));

    await dispatch(bulkMarkStudentSession({
      id: currentSession.id,
      payload: { records },
    }));
    dispatch(fetchStudentSessionById(currentSession.id));
  }

  async function handleSubmitSession() {
    if (!currentSession?.id) {
      return;
    }

    await dispatch(submitStudentSession(currentSession.id));
    dispatch(fetchStudentSessionById(currentSession.id));
  }

  async function handleLockSession() {
    if (!currentSession?.id) {
      return;
    }

    await dispatch(lockStudentSession(currentSession.id));
    dispatch(fetchStudentSessionById(currentSession.id));
  }

  return (
    <AttendancePageShell
      title="Bulk Attendance Marking"
      description="Select a session, mark the roster in a fast grid, then submit and lock the session when the class register is final."
    >
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <TextField
            select
            fullWidth
            label="Attendance Session"
            value={sessionId}
            onChange={(event) => {
              setSessionId(event.target.value);
              setSearchParams(event.target.value ? { sessionId: event.target.value } : {});
            }}
          >
            <MenuItem value="">Select session</MenuItem>
            {availableSessions.map((item) => (
              <MenuItem key={item.id} value={item.id}>
                {item.attendance_date} - {item.school_class?.name || 'Class'} {item.section?.name || ''} ({item.session_type})
              </MenuItem>
            ))}
          </TextField>

          {!sessionId && !loading ? (
            <Alert severity="info">Pick an attendance session from daily or period attendance to begin bulk marking.</Alert>
          ) : null}
        </Stack>
      </Paper>

      <PermissionGate permission="attendance.manage">
        <AttendanceBulkMarkingCard
          session={currentSession}
          students={sessionStudents}
          records={recordMap}
          statusTypes={options.statusTypes}
          saving={saving}
          error={error}
          onRecordChange={handleRecordChange}
          onSave={handleSave}
          onSubmitSession={handleSubmitSession}
          onLockSession={handleLockSession}
        />
      </PermissionGate>
    </AttendancePageShell>
  );
}
