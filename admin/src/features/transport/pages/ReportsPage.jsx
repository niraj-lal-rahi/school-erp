import { Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchTransportReports } from '../store/transportSlice';

function SummaryCard({ title, value, hint }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={0.5}>
        <Typography variant="overline" color="text.secondary">{title}</Typography>
        <Typography variant="h4">{value}</Typography>
        <Typography variant="body2" color="text.secondary">{hint}</Typography>
      </Stack>
    </Paper>
  );
}

export function ReportsPage() {
  const dispatch = useAppDispatch();
  const { reports, loading } = useAppSelector((state) => state.transport);

  useEffect(() => {
    dispatch(fetchTransportReports());
  }, [dispatch]);

  const summary = reports?.summary || {};
  const trips = reports?.trips?.data || reports?.trips?.items || [];

  return (
    <Stack spacing={3}>
      <Stack spacing={0.5}>
        <Typography variant="h4">Transport Reports</Typography>
        <Typography variant="body2" color="text.secondary">
          Monitor fleet utilization, trip completion, and passenger movement from one operations dashboard.
        </Typography>
      </Stack>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <SummaryCard title="Total Trips" value={summary.total_trips || 0} hint="All matched trips" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <SummaryCard title="Completed" value={summary.completed_trips || 0} hint="Closed successfully" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <SummaryCard title="In Progress" value={summary.in_progress_trips || 0} hint="Active right now" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <SummaryCard title="Cancelled" value={summary.cancelled_trips || 0} hint="Operational misses" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 2.4 }}>
          <SummaryCard title="Passengers" value={`${summary.total_boarded || 0} / ${summary.total_dropped || 0}`} hint="Boarded vs dropped" />
        </Grid>
      </Grid>

      <AppDataTable
        title="Recent Trips"
        columns={[
          { key: 'trip_date', header: 'Date' },
          { key: 'route', header: 'Route', render: (row) => row.route?.name || 'N/A' },
          { key: 'vehicle', header: 'Vehicle', render: (row) => row.vehicle?.vehicle_no || 'N/A' },
          { key: 'driver', header: 'Driver', render: (row) => row.driver?.full_name || 'N/A' },
          { key: 'trip_type', header: 'Type' },
          { key: 'status', header: 'Status' },
          { key: 'total_boarded', header: 'Boarded' },
          { key: 'total_dropped', header: 'Dropped' },
        ]}
        rows={trips}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No report trips available."
      />
    </Stack>
  );
}
