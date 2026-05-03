import CloudUploadOutlinedIcon from '@mui/icons-material/CloudUploadOutlined';
import TaskAltOutlinedIcon from '@mui/icons-material/TaskAltOutlined';
import { Alert, Button, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { PlatformPageShell } from '../components/PlatformPageShell';
import { platformApi } from '../services/platformApi';

export function TenantProvisioningStatusPage() {
  const { tenantId } = useParams();
  const [tenant, setTenant] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    async function load() {
      if (!tenantId) {
        return;
      }

      setLoading(true);
      setError('');

      try {
        const response = await platformApi.getTenant(tenantId);
        if (active) {
          setTenant(response.data?.data || response.data);
        }
      } catch (requestError) {
        if (active) {
          setError(requestError?.response?.data?.message || 'Unable to load tenant provisioning status.');
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
  }, [tenantId]);

  return (
    <PlatformPageShell
      title="Tenant Provisioning Status"
      description="Track whether the tenant control-plane record exists, whether the database isolation foundation is expected, and where a manual provisioning trigger will plug in next."
      extraNavItems={tenantId ? [
        { label: 'Tenant Detail', to: `/platform/tenants/${tenantId}` },
        { label: 'Database', to: `/platform/tenants/${tenantId}/database` },
        { label: 'Security', to: `/platform/tenants/${tenantId}/security` },
      ] : []}
      actions={(
        <Button variant="contained" startIcon={<CloudUploadOutlinedIcon />} disabled>
          Provision Tenant Database
        </Button>
      )}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {loading && !tenant ? <Alert severity="info">Loading provisioning posture...</Alert> : null}
      <Alert severity="warning">
        The backend provisioning service exists, but a dedicated admin-trigger endpoint has not been exposed yet. This screen shows the operational foundation and keeps the future action visible without presenting a fake working button.
      </Alert>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <TaskAltOutlinedIcon color="primary" />
              <Typography variant="h6">Central Tenant Record</Typography>
              <Typography variant="body2" color="text.secondary">
                {tenant ? `Present for ${tenant.name}` : 'Pending tenant lookup'}
              </Typography>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <TaskAltOutlinedIcon color="primary" />
              <Typography variant="h6">Isolation Target</Typography>
              <Typography variant="body2" color="text.secondary">
                {tenantId ? `tenant_${tenantId} / erp_${tenant?.code || 'tenant'}` : 'Derived from tenant code during provisioning'}
              </Typography>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <TaskAltOutlinedIcon color="primary" />
              <Typography variant="h6">Seed & Migration Readiness</Typography>
              <Typography variant="body2" color="text.secondary">
                Curated migration and safe default seeding are available in the backend provisioning service.
              </Typography>
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </PlatformPageShell>
  );
}
