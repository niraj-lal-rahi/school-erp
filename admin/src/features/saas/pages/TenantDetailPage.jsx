import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import PauseCircleOutlineOutlinedIcon from '@mui/icons-material/PauseCircleOutlineOutlined';
import StopCircleOutlinedIcon from '@mui/icons-material/StopCircleOutlined';
import { Alert, Button, Chip, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { activateTenant, cancelTenant, fetchTenant, suspendTenant } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';
import { useSaasAccess } from '../hooks/useSaasAccess';

export function TenantDetailPage() {
  const { tenantId } = useParams();
  const dispatch = useAppDispatch();
  const { selectedTenant, loading, saving, error } = useAppSelector((state) => state.saas);
  const { canManage } = useSaasAccess();

  useEffect(() => {
    if (tenantId) {
      dispatch(fetchTenant(tenantId));
    }
  }, [dispatch, tenantId]);

  const tenant = selectedTenant;

  return (
    <SaasPageShell
      title="Tenant Detail"
      description="Review school identity, commercial state, and access lifecycle before making subscription or operational changes."
      actions={canManage && tenant ? (
        <Stack direction={{ xs: 'column', sm: 'row' }} spacing={1}>
          <Button variant="outlined" startIcon={<CheckCircleOutlineOutlinedIcon />} disabled={saving} onClick={() => dispatch(activateTenant(tenant.id))}>
            Activate
          </Button>
          <Button variant="outlined" color="warning" startIcon={<PauseCircleOutlineOutlinedIcon />} disabled={saving} onClick={() => dispatch(suspendTenant(tenant.id))}>
            Suspend
          </Button>
          <Button variant="outlined" color="error" startIcon={<StopCircleOutlinedIcon />} disabled={saving} onClick={() => dispatch(cancelTenant(tenant.id))}>
            Cancel
          </Button>
        </Stack>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 7 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Stack direction="row" justifyContent="space-between" alignItems="center">
                <Typography variant="h5">{tenant?.name || 'Loading tenant...'}</Typography>
                {tenant?.status ? <Chip label={tenant.status} color={tenant.status === 'active' ? 'success' : tenant.status === 'trial' ? 'warning' : 'default'} /> : null}
              </Stack>
              <Typography variant="body2" color="text.secondary">
                Code: {tenant?.code || 'N/A'} | Domain: {tenant?.domain || 'N/A'} | Subdomain: {tenant?.subdomain || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Contact: {tenant?.email || 'N/A'} | {tenant?.phone || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Location: {[tenant?.city, tenant?.state, tenant?.country].filter(Boolean).join(', ') || 'Not configured'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Timezone: {tenant?.timezone || 'Asia/Kolkata'} | Currency: {tenant?.currency || 'INR'}
              </Typography>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 5 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <Typography variant="h5">Subscription Snapshot</Typography>
              <Typography variant="body2" color="text.secondary">
                {tenant?.active_subscription?.subscription_plan?.name || 'No active plan'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Billing cycle: {tenant?.active_subscription?.billing_cycle || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Status: {tenant?.active_subscription?.status || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Trial ends: {tenant?.trial_ends_at || tenant?.active_subscription?.trial_ends_at || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Activated at: {tenant?.activated_at || 'N/A'}
              </Typography>
            </Stack>
          </Paper>
        </Grid>
      </Grid>

      {loading && !tenant ? <Alert severity="info">Loading tenant details...</Alert> : null}
    </SaasPageShell>
  );
}
