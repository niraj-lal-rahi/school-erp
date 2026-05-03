import PlayArrowOutlinedIcon from '@mui/icons-material/PlayArrowOutlined';
import VpnKeyOutlinedIcon from '@mui/icons-material/VpnKeyOutlined';
import { Alert, Button, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { PlatformPageShell } from '../components/PlatformPageShell';
import { platformApi } from '../services/platformApi';

export function TenantDatabaseConnectionPage() {
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
          setError(requestError?.response?.data?.message || 'Unable to load tenant database posture.');
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
      title="Tenant Database Connection"
      description="Review the isolated database posture for this tenant and keep credentials masked while we expose only safe operational metadata."
      extraNavItems={tenantId ? [
        { label: 'Tenant Detail', to: `/platform/tenants/${tenantId}` },
        { label: 'Provisioning', to: `/platform/tenants/${tenantId}/provisioning` },
        { label: 'Security', to: `/platform/tenants/${tenantId}/security` },
      ] : []}
      actions={(
        <Button variant="outlined" startIcon={<PlayArrowOutlinedIcon />} disabled>
          Test Connection
        </Button>
      )}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {loading && !tenant ? <Alert severity="info">Loading database posture...</Alert> : null}
      <Alert severity="info">
        The platform backend currently protects tenant database credentials centrally, but it does not yet expose a dedicated read or test-connection endpoint to the admin UI. This screen intentionally keeps credentials masked and shows readiness state only.
      </Alert>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <Typography variant="h6">Connection Identity</Typography>
              <Typography variant="body2" color="text.secondary">
                Tenant: {tenant?.name || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Platform code: {tenant?.code || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Expected connection slot: {tenantId ? `tenant_${tenantId}` : 'tenant_{id}'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Credentials visibility: masked by design
              </Typography>
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, md: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <Stack direction="row" spacing={1.25} alignItems="center">
                <VpnKeyOutlinedIcon color="primary" />
                <Typography variant="h6">Masked Credentials</Typography>
              </Stack>
              <Typography variant="body2" color="text.secondary">Host: ********</Typography>
              <Typography variant="body2" color="text.secondary">Port: ********</Typography>
              <Typography variant="body2" color="text.secondary">Username: ********</Typography>
              <Typography variant="body2" color="text.secondary">Password: ********</Typography>
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </PlatformPageShell>
  );
}
