import DoneAllOutlinedIcon from '@mui/icons-material/DoneAllOutlined';
import { Alert, Button } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalNotificationBadge } from '../components/PortalNotificationBadge';
import { PortalPageShell } from '../components/PortalPageShell';
import { usePortalContext } from '../hooks/usePortalContext';
import {
  fetchPortalContext,
  fetchPortalNotifications,
  markAllPortalNotificationsRead,
  markPortalNotificationRead,
} from '../store/portalSlice';

export function NotificationsPage() {
  const dispatch = useAppDispatch();
  const { notifications, notificationsMeta, sectionLoading, saving, error } = usePortalContext();
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchPortalNotifications({ page, per_page: 15 }));
  }, [dispatch, page]);

  return (
    <PortalPageShell
      title="Notifications"
      description="This is the detailed portal inbox for read states and follow-up hygiene across attendance alerts, fee notices, results, transport, and general updates."
      modeLabel="Notifications"
      actions={(
        <Button
          variant="outlined"
          startIcon={<DoneAllOutlinedIcon />}
          disabled={saving || !notifications.length}
          onClick={() => dispatch(markAllPortalNotificationsRead())}
        >
          Mark All Read
        </Button>
      )}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <AppDataTable
        title="Portal Notifications"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'student', header: 'Student', render: (row) => row.student?.full_name || '-' },
          { key: 'notification_type', header: 'Type', render: (row) => <PortalNotificationBadge value={row.notification_type} /> },
          { key: 'message', header: 'Message' },
          { key: 'read_state', header: 'Read State', render: (row) => row.is_read ? 'Read' : 'Unread' },
          { key: 'created_at', header: 'Created' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => row.is_read ? 'Read' : (
              <Button size="small" onClick={() => dispatch(markPortalNotificationRead(row.id))}>
                Mark Read
              </Button>
            ),
          },
        ]}
        rows={notifications}
        loading={sectionLoading}
        searchValue=""
        onSearchChange={() => {}}
        pagination={{
          page,
          totalPages: notificationsMeta.lastPage,
          onPageChange: setPage,
        }}
        emptyState="No portal notifications are available."
      />
    </PortalPageShell>
  );
}
