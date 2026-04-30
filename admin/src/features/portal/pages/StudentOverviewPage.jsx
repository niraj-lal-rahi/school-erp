import { Alert, Grid, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalMetricCard } from '../components/PortalMetricCard';
import { PortalPageShell } from '../components/PortalPageShell';
import { PortalSectionCard } from '../components/PortalSectionCard';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, fetchPortalOverview } from '../store/portalSlice';

export function StudentOverviewPage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, overview, sectionLoading, error, dashboard } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalOverview(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  const student = overview?.student;
  const fees = overview?.fees?.summary || {};
  const attendance = overview?.attendance?.summary || {};
  const latestResult = overview?.results?.latest_result;
  const permissions = overview?.permissions || dashboard?.dashboard?.permissions || {};

  return (
    <PortalPageShell
      title="Student Overview"
      description="This is the shared summary layer for both student self-access and guardian child access, so the same screen explains who is active and what can be viewed."
      modeLabel="Overview"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <PortalMetricCard label="Student" value={student?.full_name || '-'} helper={student?.admission_no || 'No active student selected yet.'} />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <PortalMetricCard label="Attendance %" value={attendance.percentage !== undefined && attendance.percentage !== null ? `${Number(attendance.percentage).toFixed(2)}%` : '-'} helper={`Present ${attendance.present_days || 0} of ${attendance.total_days || 0} tracked days`} />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <PortalMetricCard label="Fees Due" value={fees.total_due !== undefined ? `₹${Number(fees.total_due).toFixed(2)}` : '-'} helper={fees.can_pay_fees ? 'Fee payment is enabled for this access profile.' : 'Fee payment is disabled for this access profile.'} />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <PortalMetricCard label="Latest Grade" value={latestResult?.grade || '-'} helper={latestResult?.exam?.name || 'No published result found yet.'} />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 7 }}>
          <PortalSectionCard title="Student Record" subtitle="Identity and enrollment clues for the active portal student.">
            <Stack spacing={1}>
              <Typography><strong>Full Name:</strong> {student?.full_name || '-'}</Typography>
              <Typography><strong>Admission No:</strong> {student?.admission_no || '-'}</Typography>
              <Typography><strong>Roll No:</strong> {student?.roll_no || '-'}</Typography>
              <Typography><strong>Email:</strong> {student?.email || '-'}</Typography>
              <Typography><strong>Phone:</strong> {student?.phone || '-'}</Typography>
            </Stack>
          </PortalSectionCard>
        </Grid>
        <Grid size={{ xs: 12, lg: 5 }}>
          <PortalSectionCard title="Access Permissions" subtitle="These flags come from the portal access map that controls what the current login can see for this student.">
            <Stack spacing={1}>
              <Typography><strong>Attendance:</strong> {permissions.can_view_attendance ? 'Allowed' : 'Restricted'}</Typography>
              <Typography><strong>Fees:</strong> {permissions.can_view_fees ? 'Allowed' : 'Restricted'}</Typography>
              <Typography><strong>Fee Payment:</strong> {permissions.can_pay_fees ? 'Allowed' : 'Restricted'}</Typography>
              <Typography><strong>Results:</strong> {permissions.can_view_results ? 'Allowed' : 'Restricted'}</Typography>
              <Typography><strong>Documents:</strong> {permissions.can_view_documents ? 'Allowed' : 'Restricted'}</Typography>
              <Typography><strong>Message Teacher:</strong> {permissions.can_message_teacher ? 'Allowed' : 'Restricted'}</Typography>
            </Stack>
          </PortalSectionCard>
        </Grid>
      </Grid>

      <PortalSectionCard title="Today’s Timetable Snapshot" subtitle="A quick glance at the next timetable rows for the active student.">
        <AppDataTable
          title="Upcoming Classes"
          columns={[
            { key: 'period', header: 'Period', render: (row) => row.period?.name || '-' },
            { key: 'subject', header: 'Subject', render: (row) => row.subject || row.entry_type || '-' },
            { key: 'teacher', header: 'Teacher', render: (row) => row.teacher || '-' },
            { key: 'room', header: 'Room', render: (row) => row.room || '-' },
          ]}
          rows={overview?.timetable?.upcoming || []}
          loading={sectionLoading}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No timetable entries available for the selected student."
        />
      </PortalSectionCard>
    </PortalPageShell>
  );
}
