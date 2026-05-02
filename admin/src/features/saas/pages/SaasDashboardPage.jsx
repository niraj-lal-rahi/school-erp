import AddBusinessOutlinedIcon from '@mui/icons-material/AddBusinessOutlined';
import { Alert, Button, Chip, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SaasPageShell } from '../components/SaasPageShell';
import { SaasStatCard } from '../components/SaasStatCard';
import { fetchPlans, fetchTenants } from '../store/saasSlice';
import { tenantStatusOptions } from '../types/options';

export function SaasDashboardPage() {
  const dispatch = useAppDispatch();
  const { tenants, plans, loading, error } = useAppSelector((state) => state.saas);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');

  useEffect(() => {
    dispatch(fetchTenants({ search, status }));
    dispatch(fetchPlans());
  }, [dispatch, search, status]);

  const stats = useMemo(() => {
    const active = tenants.filter((tenant) => tenant.status === 'active').length;
    const trial = tenants.filter((tenant) => tenant.status === 'trial').length;
    const suspended = tenants.filter((tenant) => tenant.status === 'suspended').length;
    const monthlyRevenue = tenants.reduce((sum, tenant) => {
      const amount = Number(tenant.active_subscription?.subscription_plan?.price_monthly || 0);
      return sum + amount;
    }, 0);

    return { active, trial, suspended, monthlyRevenue };
  }, [tenants]);

  return (
    <SaasPageShell
      title="SaaS Command Center"
      description="Keep a steady view on tenant health, trial conversion, plan mix, and platform readiness from one multi-tenant operations dashboard."
      actions={(
        <Button variant="contained" startIcon={<AddBusinessOutlinedIcon />} href="/saas/tenants/new">
          Create Tenant
        </Button>
      )}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 3 }}>
          <SaasStatCard label="Tenants" value={tenants.length} helper="Total schools currently visible in the SaaS workspace." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <SaasStatCard label="Active Schools" value={stats.active} helper="Schools with live subscriptions and active access." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <SaasStatCard label="Trials Running" value={stats.trial} helper="Schools still inside their evaluation window." />
        </Grid>
        <Grid size={{ xs: 12, md: 3 }}>
          <SaasStatCard label="Projected MRR" value={`₹${stats.monthlyRevenue.toLocaleString()}`} helper="Based on current plan pricing snapshots." />
        </Grid>
      </Grid>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Tenant Health"
            columns={[
              { key: 'name', header: 'School' },
              { key: 'code', header: 'Code' },
              { key: 'status', header: 'Status', render: (row) => <Chip size="small" label={row.status} color={row.status === 'active' ? 'success' : row.status === 'trial' ? 'warning' : 'default'} /> },
              { key: 'plan', header: 'Plan', render: (row) => row.active_subscription?.subscription_plan?.name || 'Not subscribed' },
              { key: 'subdomain', header: 'Subdomain', render: (row) => row.subdomain || 'N/A' },
            ]}
            rows={tenants}
            loading={loading}
            searchValue={search}
            onSearchChange={setSearch}
            filters={[
              {
                key: 'status',
                label: 'Status',
                value: status,
                onChange: setStatus,
                options: tenantStatusOptions,
              },
            ]}
            emptyState="No tenants available for the selected filter."
          />
        </Grid>
        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h5">Plan Snapshot</Typography>
              <Typography variant="body2" color="text.secondary">
                Compare the commercial packaging that is currently available to new and existing schools.
              </Typography>
              {plans.map((plan) => (
                <Stack key={plan.id} spacing={0.5} sx={{ p: 2, borderRadius: 2, bgcolor: 'rgba(11,110,79,0.04)' }}>
                  <Stack direction="row" justifyContent="space-between" alignItems="center">
                    <Typography fontWeight={600}>{plan.name}</Typography>
                    <Chip size="small" label={plan.status} />
                  </Stack>
                  <Typography variant="body2" color="text.secondary">
                    {plan.currency} {Number(plan.price_monthly || 0).toLocaleString()} / month
                  </Typography>
                  <Typography variant="caption" color="text.secondary">
                    Students: {plan.max_students ?? 'Unlimited'} | Staff: {plan.max_staff ?? 'Unlimited'}
                  </Typography>
                </Stack>
              ))}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </SaasPageShell>
  );
}
