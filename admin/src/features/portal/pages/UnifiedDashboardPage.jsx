import NotificationsOutlinedIcon from '@mui/icons-material/NotificationsOutlined';
import { Alert, Button, Grid, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalMetricCard } from '../components/PortalMetricCard';
import { PortalNotificationBadge } from '../components/PortalNotificationBadge';
import { PortalPageShell } from '../components/PortalPageShell';
import { PortalSectionCard } from '../components/PortalSectionCard';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, fetchPortalDashboard, fetchPortalNotifications, markPortalNotificationRead } from '../store/portalSlice';

export function UnifiedDashboardPage() {
  const dispatch = useAppDispatch();
  const { dashboard, sectionLoading, loading, error, activeContext, notifications } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
    dispatch(fetchPortalDashboard());
    dispatch(fetchPortalNotifications({ per_page: 5 }));
  }, [dispatch]);

  const dashboardPayload = dashboard?.dashboard || null;
  const kpis = dashboardPayload?.kpis || {};
  const upcomingClasses = dashboardPayload?.timetable?.upcoming || [];
  const latestResult = dashboardPayload?.results?.latest_result;
  const student = dashboardPayload?.student || activeContext?.active_student;
  const modeLabel = dashboard?.mode === 'parent_dashboard' ? 'Parent Dashboard' : 'Student Dashboard';

  return (
    <PortalPageShell
      title="Unified Portal Dashboard"
      description="One shared workspace for student self-service and guardian oversight, with the active profile and selected child driving the exact data you see."
      modeLabel={modeLabel}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 6, xl: 2.4 }}>
          <PortalMetricCard label="Active Student" value={student?.full_name || 'Select a student'} helper={student?.admission_no || 'The current portal context drives every view.'} />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 2.4 }}>
          <PortalMetricCard label="Attendance" value={kpis.attendance_percentage !== null && kpis.attendance_percentage !== undefined ? `${Number(kpis.attendance_percentage).toFixed(2)}%` : '-'} helper="Latest attendance percentage available in the portal." />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 2.4 }}>
          <PortalMetricCard label="Pending Fees" value={kpis.pending_fees !== null && kpis.pending_fees !== undefined ? `₹${Number(kpis.pending_fees).toFixed(2)}` : '-'} helper="Outstanding dues for the selected student." />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 2.4 }}>
          <PortalMetricCard label="Latest Result" value={kpis.latest_result_percentage !== null && kpis.latest_result_percentage !== undefined ? `${Number(kpis.latest_result_percentage).toFixed(2)}%` : '-'} helper="Most recent published result percentage." />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 2.4 }}>
          <PortalMetricCard label="Unread Updates" value={kpis.unread_announcements ?? 0} helper="Unread announcement count for the selected student." />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <PortalSectionCard
            title="Today and Next Up"
            subtitle="A compact view of the selected student’s upcoming timetable items."
          >
            <AppDataTable
              title="Upcoming Classes"
              columns={[
                { key: 'period', header: 'Period', render: (row) => row.period?.name || '-' },
                { key: 'subject', header: 'Subject', render: (row) => row.subject || row.entry_type || '-' },
                { key: 'teacher', header: 'Teacher', render: (row) => row.teacher || '-' },
                { key: 'room', header: 'Room', render: (row) => row.room || '-' },
              ]}
              rows={upcomingClasses}
              loading={loading || sectionLoading}
              searchValue=""
              onSearchChange={() => {}}
              emptyState="No timetable rows are lined up for the active student right now."
            />
          </PortalSectionCard>
        </Grid>

        <Grid size={{ xs: 12, lg: 6 }}>
          <PortalSectionCard
            title="Latest Result Snapshot"
            subtitle="A fast read on the most recent published examination result."
          >
            {latestResult ? (
              <Stack spacing={1.25}>
                <Typography variant="body1"><strong>Exam:</strong> {latestResult.exam?.name || 'N/A'}</Typography>
                <Typography variant="body1"><strong>Percentage:</strong> {Number(latestResult.percentage || 0).toFixed(2)}%</Typography>
                <Typography variant="body1"><strong>Grade:</strong> {latestResult.grade || 'N/A'}</Typography>
                <Typography variant="body1"><strong>Rank:</strong> {latestResult.rank || 'N/A'}</Typography>
              </Stack>
            ) : (
              <Typography color="text.secondary">No published result is available yet for this student.</Typography>
            )}
          </PortalSectionCard>
        </Grid>
      </Grid>

      <PortalSectionCard
        title="Recent Notifications"
        subtitle="Announcements, fee alerts, attendance flags, and message events all land here in one unified stream."
      >
        <AppDataTable
          title="Portal Updates"
          columns={[
            { key: 'title', header: 'Title' },
            { key: 'notification_type', header: 'Type', render: (row) => <PortalNotificationBadge value={row.notification_type} /> },
            { key: 'message', header: 'Message' },
            { key: 'created_at', header: 'Received', render: (row) => row.created_at || '-' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                row.is_read ? (
                  <Typography variant="body2" color="text.secondary">Read</Typography>
                ) : (
                  <Button
                    size="small"
                    startIcon={<NotificationsOutlinedIcon />}
                    onClick={() => dispatch(markPortalNotificationRead(row.id))}
                  >
                    Mark Read
                  </Button>
                )
              ),
            },
          ]}
          rows={notifications}
          loading={sectionLoading}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No portal notifications have been delivered yet."
        />
      </PortalSectionCard>
    </PortalPageShell>
  );
}
