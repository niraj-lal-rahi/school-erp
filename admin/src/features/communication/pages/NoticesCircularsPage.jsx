import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { CommunicationStatusBadge } from '../components/CommunicationStatusBadge';
import { fetchAnnouncements } from '../store/communicationSlice';
import { announcementTypeOptions, statusOptions } from '../types/options';

export function NoticesCircularsPage() {
  const dispatch = useAppDispatch();
  const { announcements, announcementsPagination, loading } = useAppSelector((state) => state.communication);
  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('academic');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchAnnouncements({
      page,
      per_page: 10,
      search,
      announcement_type: typeFilter || undefined,
      status: statusFilter || undefined,
    }));
  }, [dispatch, page, search, typeFilter, statusFilter]);

  return (
    <CommunicationPageShell
      title="Notice & Circular Management"
      description="Track publish-ready circulars, academic notices, and time-sensitive bulletin-style communication in a filtered view."
    >
      <AppDataTable
        title="Notices & Circulars"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'announcement_type', header: 'Type' },
          { key: 'audience_type', header: 'Audience' },
          { key: 'publish_at', header: 'Publish At', render: (row) => row.publish_at || 'Immediate' },
          { key: 'status', header: 'Status', render: (row) => <CommunicationStatusBadge value={row.status} /> },
        ]}
        rows={announcements}
        loading={loading}
        searchValue={search}
        onSearchChange={(value) => {
          setSearch(value);
          setPage(1);
        }}
        filters={[
          {
            key: 'type',
            label: 'Type',
            value: typeFilter,
            onChange: (value) => {
              setTypeFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...announcementTypeOptions],
          },
          {
            key: 'status',
            label: 'Status',
            value: statusFilter,
            onChange: (value) => {
              setStatusFilter(value);
              setPage(1);
            },
            options: [{ value: '', label: 'All' }, ...statusOptions.announcement],
          },
        ]}
        pagination={{ page, totalPages: announcementsPagination.totalPages, onPageChange: setPage }}
        emptyState="No notices or circulars found."
      />
    </CommunicationPageShell>
  );
}
