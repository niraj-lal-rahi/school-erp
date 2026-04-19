import GroupsOutlinedIcon from '@mui/icons-material/GroupsOutlined';
import HowToRegOutlinedIcon from '@mui/icons-material/HowToRegOutlined';
import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import ViewKanbanOutlinedIcon from '@mui/icons-material/ViewKanbanOutlined';
import {
  Alert,
  Grid,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../components/common/AppDataTable';
import { dashboardApi } from './dashboardApi';

function MetricCard({ label, value, icon, tone = 'rgba(11,110,79,0.12)' }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)', background: `linear-gradient(180deg, #fff 0%, ${tone} 100%)` }}>
      <Stack spacing={1}>
        <div>{icon}</div>
        <Typography variant="body2" color="text.secondary">{label}</Typography>
        <Typography variant="h4">{value}</Typography>
      </Stack>
    </Paper>
  );
}

export function DashboardPage() {
  const [data, setData] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    dashboardApi.getOverview()
      .then((response) => setData(response.data.data))
      .catch((nextError) => setError(nextError.response?.data?.message || 'Failed to load dashboard.'));
  }, []);

  return (
    <Stack spacing={3}>
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <MetricCard label="Total Students" value={data?.students?.total || 0} icon={<SchoolOutlinedIcon color="primary" />} />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <MetricCard label="Active Students" value={data?.students?.active || 0} icon={<HowToRegOutlinedIcon color="success" />} tone="rgba(42,157,143,0.12)" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <MetricCard label="Guardians" value={data?.guardians_total || 0} icon={<GroupsOutlinedIcon color="secondary" />} tone="rgba(244,162,97,0.12)" />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <MetricCard label="Sections" value={data?.sections_total || 0} icon={<ViewKanbanOutlinedIcon color="action" />} tone="rgba(69,123,157,0.12)" />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 5 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Typography variant="h6" gutterBottom>Admission Funnel</Typography>
            <Stack spacing={1.5}>
              <Typography>Applied: {data?.admissions?.applied || 0}</Typography>
              <Typography>Reviewing: {data?.admissions?.reviewing || 0}</Typography>
              <Typography>Admitted: {data?.admissions?.admitted || 0}</Typography>
              <Typography>Inactive: {data?.students?.inactive || 0}</Typography>
              <Typography>Alumni: {data?.students?.alumni || 0}</Typography>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 7 }}>
          <AppDataTable
            title="Recent Students"
            columns={[
              { key: 'name', header: 'Student', render: (row) => `${row.first_name} ${row.last_name}` },
              { key: 'admission_no', header: 'Admission No' },
              { key: 'status', header: 'Status' },
              { key: 'created_at', header: 'Created At' },
            ]}
            rows={data?.recent_students || []}
            loading={!data && !error}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No recent students available."
          />
        </Grid>
      </Grid>
    </Stack>
  );
}
