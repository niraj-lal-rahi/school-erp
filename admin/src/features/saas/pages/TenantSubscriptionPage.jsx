import AutorenewOutlinedIcon from '@mui/icons-material/AutorenewOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { changeTenantPlan, fetchPlans, fetchTenant, fetchTenants, subscribeTenant } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';
import { billingCycleOptions } from '../types/options';

export function TenantSubscriptionPage() {
  const dispatch = useAppDispatch();
  const { tenants, plans, selectedTenant, saving, error } = useAppSelector((state) => state.saas);
  const [tenantId, setTenantId] = useState('');
  const [payload, setPayload] = useState({
    subscription_plan_id: '',
    billing_cycle: 'monthly',
    start_date: new Date().toISOString().slice(0, 10),
    status: 'active',
    auto_renew: true,
  });

  useEffect(() => {
    dispatch(fetchTenants());
    dispatch(fetchPlans());
  }, [dispatch]);

  useEffect(() => {
    if (!tenants.length) {
      return;
    }

    if (!tenantId) {
      setTenantId(String(tenants[0].id));
    }
  }, [tenants, tenantId]);

  useEffect(() => {
    if (tenantId) {
      dispatch(fetchTenant(tenantId));
    }
  }, [dispatch, tenantId]);

  const submitSubscription = async () => {
    if (!tenantId) {
      return;
    }

    await dispatch(subscribeTenant({ id: tenantId, payload }));
    dispatch(fetchTenant(tenantId));
  };

  const submitPlanChange = async () => {
    if (!tenantId) {
      return;
    }

    await dispatch(changeTenantPlan({ id: tenantId, payload }));
    dispatch(fetchTenant(tenantId));
  };

  return (
    <SaasPageShell
      title="Tenant Subscription"
      description="Start, replace, and review subscription lifecycles for any school from one guided workflow."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <TextField
                select
                label="Tenant"
                value={tenantId}
                onChange={(event) => setTenantId(event.target.value)}
              >
                {tenants.map((tenant) => (
                  <MenuItem key={tenant.id} value={tenant.id}>
                    {tenant.name}
                  </MenuItem>
                ))}
              </TextField>
              <TextField
                select
                label="Plan"
                value={payload.subscription_plan_id}
                onChange={(event) => setPayload((current) => ({ ...current, subscription_plan_id: event.target.value }))}
              >
                {plans.map((plan) => (
                  <MenuItem key={plan.id} value={plan.id}>
                    {plan.name}
                  </MenuItem>
                ))}
              </TextField>
              <TextField
                select
                label="Billing Cycle"
                value={payload.billing_cycle}
                onChange={(event) => setPayload((current) => ({ ...current, billing_cycle: event.target.value }))}
              >
                {billingCycleOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </TextField>
              <TextField
                type="date"
                label="Start Date"
                value={payload.start_date}
                onChange={(event) => setPayload((current) => ({ ...current, start_date: event.target.value }))}
                InputLabelProps={{ shrink: true }}
              />
              <Stack direction={{ xs: 'column', sm: 'row' }} spacing={1}>
                <Button variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving} onClick={submitSubscription}>
                  Subscribe Tenant
                </Button>
                <Button variant="outlined" startIcon={<AutorenewOutlinedIcon />} disabled={saving} onClick={submitPlanChange}>
                  Change Plan
                </Button>
              </Stack>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1.5}>
              <Typography variant="h5">Current Subscription</Typography>
              <Typography variant="body2" color="text.secondary">
                Plan: {selectedTenant?.active_subscription?.subscription_plan?.name || 'None'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Status: {selectedTenant?.active_subscription?.status || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Billing cycle: {selectedTenant?.active_subscription?.billing_cycle || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                Start date: {selectedTenant?.active_subscription?.start_date || 'N/A'}
              </Typography>
              <Typography variant="body2" color="text.secondary">
                End date: {selectedTenant?.active_subscription?.end_date || 'Open'}
              </Typography>
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </SaasPageShell>
  );
}
