import { Alert, Button, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { archiveMessage, fetchMessages, markMessageRead } from '../store/communicationSlice';
import { priorityOptions, statusOptions } from '../types/options';

export function MessageInboxPage() {
  const dispatch = useAppDispatch();
  const { messages, messagesPagination, loading, error } = useAppSelector((state) => state.communication);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [priorityFilter, setPriorityFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchMessages({
      page,
      per_page: 10,
      search,
      status: statusFilter || undefined,
      priority: priorityFilter || undefined,
    }));
  }, [dispatch, page, search, statusFilter, priorityFilter]);

  return (
    <CommunicationPageShell
      title="Message Inbox"
      description="Review delivery status, open read receipts, and keep direct or operational messages moving without leaving the module."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <AppDataTable
        title="Inbox"
        columns={[
          { key: 'subject', header: 'Subject', render: (row) => row.subject || 'No subject' },
          { key: 'recipient_type', header: 'Recipient Type' },
          { key: 'priority', header: 'Priority' },
          { key: 'status', header: 'Status', render: (row) => <CommunicationStatusBadge value={row.status} /> },
          { key: 'sent_at', header: 'Sent At', render: (row) => row.sent_at || row.created_at },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                {row.status !== 'read' ? (
                  <Button size="small" onClick={() => dispatch(markMessageRead(row.id))}>Mark Read</Button>
                ) : null}
                {row.status !== 'archived' ? (
                  <Button size="small" onClick={() => dispatch(archiveMessage(row.id))}>Archive</Button>
                ) : null}
              </Stack>
            ),
          },
        ]}
        rows={messages}
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
            options: [{ value: '', label: 'All' }, ...statusOptions.message],
          },
          {
            key: 'priority',
            label: 'Priority',
            value: priorityFilter,
            onChange: (value) => {
              setPriorityFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...priorityOptions],
          },
        ]}
        pagination={{ page, totalPages: messagesPagination.totalPages, onPageChange: setPage }}
        emptyState="No messages found in the inbox."
      />
    </CommunicationPageShell>
  );
}
