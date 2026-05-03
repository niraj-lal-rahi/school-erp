import ShieldOutlinedIcon from '@mui/icons-material/ShieldOutlined';
import { Alert, Chip, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { PlatformPageShell } from '../components/PlatformPageShell';
import { platformApi } from '../services/platformApi';

export function TenantSecuritySettingsPage() {
  const { tenantId } = useParams();
  const [tenant, setTenant] = useState(null);
  const [features, setFeatures] = useState([]);
  const [domains, setDomains] = useState([]);
  const [usage, setUsage] = useState(null);
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
        const [tenantResponse, featuresResponse, domainsResponse, usageResponse] = await Promise.allSettled([
          platformApi.getTenant(tenantId),
          platformApi.getTenantFeatures(tenantId),
          platformApi.getTenantDomains(tenantId),
          platformApi.getTenantUsage(tenantId),
        ]);

        if (!active) {
          return;
        }

        if (tenantResponse.status === 'fulfilled') {
          setTenant(tenantResponse.value.data?.data || tenantResponse.value.data);
        }

        if (featuresResponse.status === 'fulfilled') {
          const payload = featuresResponse.value.data?.data || featuresResponse.value.data || [];
          setFeatures(Array.isArray(payload) ? payload : payload?.data || []);
        }

        if (domainsResponse.status === 'fulfilled') {
          const payload = domainsResponse.value.data?.data || domainsResponse.value.data || [];
          setDomains(Array.isArray(payload) ? payload : payload?.data || []);
        }

        if (usageResponse.status === 'fulfilled') {
          setUsage(usageResponse.value.data?.data || usageResponse.value.data || null);
        }
      } catch (requestError) {
        if (active) {
          setError(requestError?.response?.data?.message || 'Unable to load tenant security posture.');
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
      title="Tenant Security Settings"
      description="Review the tenant’s isolation posture, domain verification footprint, and feature-level exposure from the platform control plane."
      extraNavItems={tenantId ? [
        { label: 'Tenant Detail', to: `/platform/tenants/${tenantId}` },
        { label: 'Database', to: `/platform/tenants/${tenantId}/database` },
        { label: 'Provisioning', to: `/platform/tenants/${tenantId}/provisioning` },
      ] : []}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {loading && !tenant ? <Alert severity="info">Loading tenant security posture...</Alert> : null}
      <Alert severity="info">
        Central tenant security settings such as emergency access and backup encryption are supported in the backend model layer, but a dedicated management endpoint is not exposed yet. This page surfaces the security-adjacent signals the platform API already provides.
      </Alert>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <Stack direction="row" spacing={1.25} alignItems="center">
                <ShieldOutlinedIcon color="primary" />
                <Typography variant="h6">Isolation Posture</Typography>
              </Stack>
              <Typography variant="body2" color="text.secondary">Tenant: {tenant?.name || 'N/A'}</Typography>
              <Typography variant="body2" color="text.secondary">Database isolated: expected yes</Typography>
              <Typography variant="body2" color="text.secondary">Encryption enabled: expected yes</Typography>
              <Typography variant="body2" color="text.secondary">Emergency access: controlled centrally when endpoint is added</Typography>
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, md: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <Typography variant="h6">Usage & Feature Exposure</Typography>
              <Typography variant="body2" color="text.secondary">
                Usage sync state: {usage?.updated_at || usage?.synced_at || 'Available through platform usage endpoint'}
              </Typography>
              <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap">
                {features.length ? features.slice(0, 8).map((feature) => (
                  <Chip
                    key={feature.id || feature.feature_code}
                    size="small"
                    label={feature.feature_code || feature.code || 'feature'}
                    color={feature.is_enabled ? 'success' : 'default'}
                  />
                )) : (
                  <Typography variant="body2" color="text.secondary">
                    No feature overrides are currently exposed for this tenant.
                  </Typography>
                )}
              </Stack>
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Typography variant="h6" gutterBottom>Verified Domains</Typography>
            <Stack spacing={1.25}>
              {domains.length ? domains.map((domain) => (
                <Stack key={domain.id} direction="row" justifyContent="space-between" alignItems="center">
                  <Typography variant="body2">{domain.domain || domain.host || 'Domain'}</Typography>
                  <Chip size="small" label={domain.is_verified ? 'verified' : (domain.status || 'pending')} color={domain.is_verified ? 'success' : 'default'} />
                </Stack>
              )) : (
                <Typography variant="body2" color="text.secondary">
                  No custom domain records are currently available from the platform API for this tenant.
                </Typography>
              )}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </PlatformPageShell>
  );
}
