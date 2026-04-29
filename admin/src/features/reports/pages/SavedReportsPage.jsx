import BookmarkBorderOutlinedIcon from '@mui/icons-material/BookmarkBorderOutlined';
import { Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ReportsMetricCard } from '../components/ReportsMetricCard';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { fetchReportDefinitions } from '../store/reportsSlice';

function SavedReportCard({ title, code, description, meta }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)', height: '100%' }}>
      <Stack spacing={1.5}>
        <Stack direction="row" spacing={1} alignItems="center">
          <BookmarkBorderOutlinedIcon color="primary" />
          <Typography variant="h6">{title}</Typography>
        </Stack>
        <Typography variant="caption" color="text.secondary">{code}</Typography>
        <Typography variant="body2" color="text.secondary">{description || 'No description available.'}</Typography>
        <Typography variant="caption" color="text.secondary">{meta}</Typography>
      </Stack>
    </Paper>
  );
}

export function SavedReportsPage() {
  const dispatch = useAppDispatch();
  const { definitions, loading } = useAppSelector((state) => state.reports);

  useEffect(() => {
    dispatch(fetchReportDefinitions({ per_page: 100 }));
  }, [dispatch]);

  const systemReports = definitions.filter((item) => item.is_system);
  const customReports = definitions.filter((item) => item.module === 'custom');

  return (
    <ReportsPageShell
      title="Saved Reports"
      description="Keep an eye on the catalog of system and custom report definitions your team relies on most."
    >
      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 4 }}>
          <ReportsMetricCard label="Total Saved Reports" value={definitions.length} helper="All active and inactive report definitions." />
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <ReportsMetricCard label="System Reports" value={systemReports.length} helper="Definitions seeded and maintained by the platform." />
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <ReportsMetricCard label="Custom Reports" value={customReports.length} helper="Tenant-specific reporting definitions." />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        {definitions.map((item) => (
          <Grid key={item.id} size={{ xs: 12, md: 6, xl: 4 }}>
            <SavedReportCard
              title={item.name}
              code={item.code}
              description={item.description}
              meta={`${item.module} · ${item.status}${loading ? ' · loading...' : ''}`}
            />
          </Grid>
        ))}
      </Grid>
    </ReportsPageShell>
  );
}
