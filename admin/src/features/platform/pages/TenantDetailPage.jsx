import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import DnsOutlinedIcon from '@mui/icons-material/DnsOutlined';
import GppGoodOutlinedIcon from '@mui/icons-material/GppGoodOutlined';
import PauseCircleOutlineOutlinedIcon from '@mui/icons-material/PauseCircleOutlineOutlined';
import StopCircleOutlinedIcon from '@mui/icons-material/StopCircleOutlined';
import { Alert, Button, Chip, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { Link as RouterLink, useParams } from 'react-router-dom';
import { PlatformPageShell } from '../components/PlatformPageShell';
import { platformApi } from '../services/platformApi';

export function TenantDetailPage() {
  const { tenantId } = useParams();
  const [tenant, setTenant] = useState(null);
  const [billing, setBilling] = useState([]);
  const [domains, setDomains] = useState([]);
  const [usage, setUsage] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
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
        const [tenantResponse, billingResponse, domainsResponse, usageResponse] = await Promise.allSettled([
          platformApi.getTenant(tenantId),
          platformApi.getTenantBilling(tenantId),
          platformApi.getTenantDomains(tenantId),
          platformApi.getTenantUsage(tenantId),
        ]);

        if (!active) {
          return;
        }

        if (tenantResponse.status === 'fulfilled') {
          setTenant(tenantResponse.value.data?.data || tenantResponse.value.data);
        } else {
          throw tenantResponse.reason;
        }

        if (billingResponse.status === 'fulfilled') {
          const payload = billingResponse.value.data?.data || billingResponse.value.data || [];
          setBilling(Array.isArray(payload) ? payload : payload?.data || []);
        }

        if (domainsResponse.status === 'fulfilled') {
          const payload = domainsResponse.value.data?.data || domainsResponse.value.data || [];
          setDomains(Array.isArray(payload) ? payload : payload?.data || []);
        }

        if (usageResponse.status === 'fulfilled') {
          setUsage(usageResponse.value.data?.data || usageResponse.value.data || null);
        }
      } catch (requestError) {
        if (!active) {
          return;
        }

        setError(requestError?.response?.data?.message || 'Unable to load the platform tenant detail right now.');
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

  const actions = useMemo(() => tenant ? (
    <Stack direction={{ xs: 'column', sm: 'row' }} spacing={1}>
      <Button
        variant="outlined"
        startIcon={<CheckCircleOutlineOutlinedIcon />}
        disabled={saving}
        onClick={async () => {
          setSaving(true);
          try {
            const response = await platformApi.activateTenant(tenant.id);
            setTenant(response.data?.data || response.data);
          } catch (requestError) {
            setError(requestError?.response?.data?.message || 'Unable to activate tenant.');
          } finally {
            setSaving(false);
          }
        }}
      >
        Activate
      </Button>
      <Button
        variant="outlined"
        color="warning"
        startIcon={<PauseCircleOutlineOutlinedIcon />}
        disabled={saving}
        onClick={async () => {
          setSaving(true);
          try {
            const response = await platformApi.suspendTenant(tenant.id);
            setTenant(response.data?.data || response.data);
          } catch (requestError) {
            setError(requestError?.response?.data?.message || 'Unable to suspend tenant.');
          } finally {
            setSaving(false);
          }
        }}
      >
        Suspend
      </Button>
      <Button
        variant="outlined"
        color="error"
        startIcon={<StopCircleOutlinedIcon />}
        disabled={saving}
        onClick={async () => {
          setSaving(true);
          try {
            const response = await platformApi.cancelTenant(tenant.id);
            setTenant(response.data?.data || response.data);
          } catch (requestError) {
            setError(requestError?.response?.data?.message || 'Unable to cancel tenant.');
          } finally {
            setSaving(false);
          }
        }}
      >
        Cancel
      </Button>
    </Stack>
  ) : null, [saving, tenant]);

  return (
    <PlatformPageShell
      title="Tenant Detail"
      description="Review the tenant’s commercial state, isolated database posture, domain footprint, and operational controls before making platform-level changes."
      actions={actions}
      extraNavItems={tenantId ? [
        { label: 'Database', to: `/platform/tenants/${tenantId}/database` },
        { label: 'Provisioning', to: `/platform/tenants/${tenantId}/provisioning` },
        { label: 'Security', to: `/platform/tenants/${tenantId}/security` },
      ] : []}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {loading && !tenant ? <Alert severity="info">Loading tenant details...</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 7 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Stack direction="row" justifyContent="space-between" alignItems="center">
                <Typography variant="h5">{tenant?.name || 'Loading tenant...'}</Typography>
                {tenant?.status ? (
                  <Chip
                    label={tenant.status}
                    color={tenant.status === 'active' ? 'success' : tenant.status === 'trial' ? 'warning' : tenant.status === 'suspended' ? 'error' : 'default'}
                  />
                ) : null}
              </Stack>
              <Typography variant="body2" color="text.secondary">
                Code: {tenant?.code || 'N/A'} | Slug: {tenant?.slug || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Contact: {tenant?.email || 'N/A'} | {tenant?.phone || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Trial ends: {tenant?.trial_ends_at || 'N/A'} | Activated: {tenant?.activated_at || 'N/A'}
              </Typography>
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, lg: 5 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <Typography variant="h5">Platform Snapshot</Typography>
              <Typography variant="body2" color="text.secondary">
                Billing records: {billing.length}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Domains registered: {domains.length}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Usage sync posture: {usage?.updated_at || usage?.synced_at || 'Available via tenant usage endpoint'}
              </Typography>
              <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap" pt={1}>
                <Button size="small" variant="outlined" component={RouterLink} to={`/platform/tenants/${tenantId}/database`} startIcon={<DnsOutlinedIcon />}>
                  Database Connection
                </Button>
                <Button size="small" variant="outlined" component={RouterLink} to={`/platform/tenants/${tenantId}/security`} startIcon={<GppGoodOutlinedIcon />}>
                  Security Settings
                </Button>
              </Stack>
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, lg: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Typography variant="h6" gutterBottom>Domain Footprint</Typography>
            <Stack spacing={1.25}>
              {domains.length ? domains.map((domain) => (
                <Stack key={domain.id} direction="row" justifyContent="space-between" alignItems="center">
                  <Typography variant="body2">{domain.domain || domain.host || 'Domain'}</Typography>
                  <Chip size="small" label={domain.status || (domain.is_verified ? 'verified' : 'pending')} color={domain.is_verified ? 'success' : 'default'} />
                </Stack>
              )) : (
                <Typography variant="body2" color="text.secondary">
                  No platform domain records are available for this tenant yet.
                </Typography>
              )}
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, lg: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Typography variant="h6" gutterBottom>Recent Billing Posture</Typography>
            <Stack spacing={1.25}>
              {billing.length ? billing.slice(0, 4).map((record) => (
                <Stack key={record.id} direction="row" justifyContent="space-between" alignItems="center">
                  <Typography variant="body2">{record.invoice_no || `Billing #${record.id}`}</Typography>
                  <Chip size="small" label={record.status || 'pending'} color={record.status === 'paid' ? 'success' : record.status === 'failed' ? 'error' : 'default'} />
                </Stack>
              )) : (
                <Typography variant="body2" color="text.secondary">
                  No billing rows are currently available from the platform API for this tenant.
                </Typography>
              )}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </PlatformPageShell>
  );
}
