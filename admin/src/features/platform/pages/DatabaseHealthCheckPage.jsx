import FavoriteBorderOutlinedIcon from '@mui/icons-material/FavoriteBorderOutlined';
import { Alert, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { PlatformPageShell } from '../components/PlatformPageShell';
import { PlatformStatCard } from '../components/PlatformStatCard';
import { platformApi } from '../services/platformApi';

export function DatabaseHealthCheckPage() {
  const [tenants, setTenants] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    async function load() {
      setLoading(true);
      setError('');

      try {
        const response = await platformApi.getTenants({ page: 1 });
        const payload = response.data?.data || response.data || {};
        const rows = Array.isArray(payload?.data) ? payload.data : Array.isArray(payload) ? payload : [];

        if (active) {
          setTenants(rows);
        }
      } catch (requestError) {
        if (active) {
          setError(requestError?.response?.data?.message || 'Unable to load platform tenant health summary.');
        }
      } finally {
        if (active) {
          setLoading(false);
        }
      }
    }

    load();

    return () => {
      active = false;
    };
  }, []);

  const metrics = useMemo(() => {
    const active = tenants.filter((tenant) => tenant.status === 'active').length;
    const suspended = tenants.filter((tenant) => tenant.status === 'suspended').length;
    const trial = tenants.filter((tenant) => tenant.status === 'trial').length;

    return { active, suspended, trial, total: tenants.length };
  }, [tenants]);

  return (
    <PlatformPageShell
      title="Database Health Check"
      description="Review a control-plane summary of tenant fleet posture while the dedicated central health-check endpoints are still being exposed."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {loading ? <Alert severity="info">Loading platform health signals...</Alert> : null}
      <Alert severity="info">
        The UI is ready for `/api/v1/system/health` style endpoints, but for now this page derives health posture from the platform tenant fleet and clearly marks the missing backend health feed.
      </Alert>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <PlatformStatCard label="Known Tenants" value={metrics.total} caption="Tenants visible from the central platform route." />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <PlatformStatCard label="Active" value={metrics.active} caption="Tenants currently marked active." />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <PlatformStatCard label="Trial" value={metrics.trial} caption="Tenants still inside trial posture." />
        </Grid>
        <Grid size={{ xs: 12, sm: 6, lg: 3 }}>
          <PlatformStatCard label="Suspended" value={metrics.suspended} caption="Tenants blocked at the control-plane layer." />
        </Grid>
      </Grid>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Stack direction="row" spacing={1.5} alignItems="center">
            <FavoriteBorderOutlinedIcon color="primary" />
            <Typography variant="h6">Health Endpoint Readiness</Typography>
          </Stack>
          <Typography variant="body2" color="text.secondary">
            Once the central system module exposes queue, cache, storage, and failed-job health endpoints, this page can switch from derived fleet posture to true infrastructure health cards without any route or navigation changes.
          </Typography>
        </Stack>
      </Paper>
    </PlatformPageShell>
  );
}
