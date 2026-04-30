import { Alert, Grid } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalMetricCard } from '../components/PortalMetricCard';
import { PortalPageShell } from '../components/PortalPageShell';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalAttendance, fetchPortalContext } from '../store/portalSlice';

export function AttendancePage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, attendance, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalAttendance(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  const summary = attendance?.summary || {};

  return (
    <PortalPageShell
      title="Attendance"
      description="Keep a simple, shared view of attendance health for the active student, whether the login belongs to the student or a guardian."
      modeLabel="Attendance"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <Grid container spacing={2}>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <PortalMetricCard label="Attendance %" value={summary.percentage !== undefined && summary.percentage !== null ? `${Number(summary.percentage).toFixed(2)}%` : '-'} helper="Overall attendance percentage." />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <PortalMetricCard label="Total Days" value={summary.total_days ?? 0} helper="Tracked attendance days in summary." />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <PortalMetricCard label="Present" value={summary.present_days ?? 0} helper="Present days recorded so far." />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <PortalMetricCard label="Absent" value={summary.absent_days ?? 0} helper="Absent days recorded so far." />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <PortalMetricCard label="Leave / Late" value={`${summary.leave_days ?? 0} / ${summary.late_days ?? 0}`} helper="Leave and late counts." />
        </Grid>
      </Grid>
    </PortalPageShell>
  );
}
