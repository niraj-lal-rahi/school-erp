import MarkEmailReadOutlinedIcon from '@mui/icons-material/MarkEmailReadOutlined';
import { Alert, Button, Grid } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalMetricCard } from '../components/PortalMetricCard';
import { PortalNotificationBadge } from '../components/PortalNotificationBadge';
import { PortalPageShell } from '../components/PortalPageShell';
import { usePortalContext } from '../hooks/usePortalContext';
import {
  fetchPortalContext,
  fetchPortalDashboard,
  fetchPortalNotifications,
  markPortalNotificationRead,
} from '../store/portalSlice';

export function AnnouncementsMessagesPage() {
  const dispatch = useAppDispatch();
  const { dashboard, notifications, notificationsMeta, sectionLoading, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
    dispatch(fetchPortalDashboard());
    dispatch(fetchPortalNotifications({ per_page: 10 }));
  }, [dispatch]);

  const dashboardPayload = dashboard?.dashboard || {};
  const unreadAnnouncements = dashboardPayload?.announcements?.unread_count || 0;
  const unreadNotifications = notifications.filter((item) => !item.is_read).length;

  return (
    <PortalPageShell
      title="Announcements and Messages"
      description="The portal keeps student-facing announcements, alerts, and message-like updates together so guardians and students do not need separate inboxes."
      modeLabel="Updates"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 6 }}>
          <PortalMetricCard label="Unread Announcements" value={unreadAnnouncements} helper="Unread announcement count derived from the active student context." />
        </Grid>
        <Grid size={{ xs: 12, md: 6 }}>
          <PortalMetricCard label="Unread Notifications" value={unreadNotifications} helper={`Showing ${notificationsMeta.total || notifications.length} portal updates in the current inbox.`} />
        </Grid>
      </Grid>

      <AppDataTable
        title="Unified Updates Stream"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'notification_type', header: 'Type', render: (row) => <PortalNotificationBadge value={row.notification_type} /> },
          { key: 'message', header: 'Message' },
          { key: 'created_at', header: 'Received' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              row.is_read ? 'Read' : (
                <Button
                  size="small"
                  startIcon={<MarkEmailReadOutlinedIcon />}
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
        emptyState="No announcement or message updates are available yet."
      />
    </PortalPageShell>
  );
}
