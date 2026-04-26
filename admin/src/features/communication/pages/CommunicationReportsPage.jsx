import { Grid, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { ReportMetricCard } from '../components/ReportMetricCard';
import { fetchAnnouncements, fetchCommunicationReports } from '../store/communicationSlice';
import { channelOptions, statusOptions } from '../types/options';

export function CommunicationReportsPage() {
  const dispatch = useAppDispatch();
  const { reports, announcements, loading } = useAppSelector((state) => state.communication);
  const [filters, setFilters] = useState({
    channel: '',
    status: '',
    announcement_id: '',
    date_from: '',
    date_to: '',
  });

  useEffect(() => {
    dispatch(fetchAnnouncements({ per_page: 100 }));
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchCommunicationReports({
      channel: filters.channel || undefined,
      status: filters.status || undefined,
      announcement_id: filters.announcement_id || undefined,
      date_from: filters.date_from || undefined,
      date_to: filters.date_to || undefined,
    }));
  }, [dispatch, filters]);

  return (
    <CommunicationPageShell
      title="Communication Reports Dashboard"
      description="Review send volume, delivery outcomes, read performance, and announcement engagement from one consolidated reporting surface."
    >
      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 3 }}>
          <TextField select fullWidth label="Channel" value={filters.channel} onChange={(event) => setFilters((current) => ({ ...current, channel: event.target.value }))}>
            <MenuItem value="">All</MenuItem>
            {channelOptions.filter((item) => item.value !== 'multi').map((item) => (
              <MenuItem key={item.value} value={item.value}>{item.label}</MenuItem>
            ))}
          </TextField>
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <TextField select fullWidth label="Status" value={filters.status} onChange={(event) => setFilters((current) => ({ ...current, status: event.target.value }))}>
            <MenuItem value="">All</MenuItem>
            {statusOptions.notification.map((item) => (
              <MenuItem key={item.value} value={item.value}>{item.label}</MenuItem>
            ))}
          </TextField>
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <TextField select fullWidth label="Announcement" value={filters.announcement_id} onChange={(event) => setFilters((current) => ({ ...current, announcement_id: event.target.value }))}>
            <MenuItem value="">All</MenuItem>
            {announcements.map((item) => (
              <MenuItem key={item.id} value={item.id}>{item.title}</MenuItem>
            ))}
          </TextField>
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <Stack direction="row" spacing={1}>
            <TextField fullWidth label="From" type="date" InputLabelProps={{ shrink: true }} value={filters.date_from} onChange={(event) => setFilters((current) => ({ ...current, date_from: event.target.value }))} />
            <TextField fullWidth label="To" type="date" InputLabelProps={{ shrink: true }} value={filters.date_to} onChange={(event) => setFilters((current) => ({ ...current, date_to: event.target.value }))} />
          </Stack>
        </Grid>
      </Grid>

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportMetricCard label="Total Sent" value={reports?.delivery?.totals?.total ?? 0} helper="All notification attempts in the filter window." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportMetricCard label="Delivered" value={reports?.delivery?.totals?.delivered ?? 0} helper="Successfully delivered notifications." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportMetricCard label="Failed" value={reports?.delivery?.totals?.failed ?? 0} helper="Items that failed or bounced." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <ReportMetricCard label="Read Rate" value={`${reports?.delivery?.totals?.read ?? 0}`} helper="Read count from current log slice." />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Delivery By Channel"
            columns={[
              { key: 'channel', header: 'Channel' },
              { key: 'total', header: 'Total' },
            ]}
            rows={reports?.delivery?.by_channel || []}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No delivery data available."
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <AppDataTable
            title="Announcement Engagement"
            columns={[
              { key: 'announcement_id', header: 'Announcement ID' },
              { key: 'total_recipients', header: 'Recipients' },
              { key: 'read_count', header: 'Read' },
              { key: 'acknowledged_count', header: 'Acknowledged' },
            ]}
            rows={reports?.engagement?.announcements || []}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No announcement engagement data available."
          />
        </Grid>
      </Grid>

      <AppDataTable
        title="Message Volume"
        columns={[
          { key: 'activity_date', header: 'Date' },
          { key: 'total', header: 'Total Messages' },
        ]}
        rows={reports?.volume?.daily_volume || []}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No message volume data available."
      />
    </CommunicationPageShell>
  );
}
