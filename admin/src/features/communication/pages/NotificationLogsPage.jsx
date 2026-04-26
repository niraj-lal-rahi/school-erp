import { Alert, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { fetchNotifications, markNotificationRead } from '../store/communicationSlice';
import { channelOptions, statusOptions } from '../types/options';

export function NotificationLogsPage() {
  const dispatch = useAppDispatch();
  const { notifications, notificationsPagination, loading, error } = useAppSelector((state) => state.communication);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [channelFilter, setChannelFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchNotifications({
      page,
      per_page: 10,
      search,
      status: statusFilter || undefined,
      channel: channelFilter || undefined,
    }));
  }, [dispatch, page, search, statusFilter, channelFilter]);

  return (
    <CommunicationPageShell
      title="Notification Logs"
      description="Monitor message delivery history across channels, troubleshoot failures, and track read progress without leaving the communication module."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <AppDataTable
        title="Notification Delivery"
        columns={[
          { key: 'subject', header: 'Subject', render: (row) => row.subject || 'No subject' },
          { key: 'channel', header: 'Channel' },
          { key: 'status', header: 'Status', render: (row) => <CommunicationStatusBadge value={row.status} /> },
          { key: 'provider', header: 'Provider', render: (row) => row.provider || 'System' },
          { key: 'sent_at', header: 'Sent At', render: (row) => row.sent_at || row.created_at },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                {row.status !== 'read' ? (
                  <button type="button" onClick={() => dispatch(markNotificationRead(row.id))} style={{ border: 0, background: 'transparent', color: '#0b6e4f', cursor: 'pointer' }}>
                    Mark Read
                  </button>
                ) : null}
              </Stack>
            ),
          },
        ]}
        rows={notifications}
        loading={loading}
        searchValue={search}
        onSearchChange={(value) => {
          setSearch(value);
          setPage(1);
        }}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: statusFilter,
            onChange: (value) => {
              setStatusFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...statusOptions.notification],
          },
          {
            key: 'channel',
            label: 'Channel',
            value: channelFilter,
            onChange: (value) => {
              setChannelFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...channelOptions.filter((item) => item.value !== 'multi')],
          },
        ]}
        pagination={{ page, totalPages: notificationsPagination.totalPages, onPageChange: setPage }}
        emptyState="No notification log entries found."
      />
    </CommunicationPageShell>
  );
}
