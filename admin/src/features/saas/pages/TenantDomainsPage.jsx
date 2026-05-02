import PublicOutlinedIcon from '@mui/icons-material/PublicOutlined';
import VerifiedOutlinedIcon from '@mui/icons-material/VerifiedOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createTenantDomain, fetchTenantDomains, fetchTenants, verifyTenantDomain } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';
import { domainTypeOptions } from '../types/options';

const initialDomain = {
  domain: '',
  domain_type: 'custom',
};

export function TenantDomainsPage() {
  const dispatch = useAppDispatch();
  const { tenants, selectedDomains, loading, saving, error } = useAppSelector((state) => state.saas);
  const [tenantId, setTenantId] = useState('');
  const [form, setForm] = useState(initialDomain);

  useEffect(() => {
    dispatch(fetchTenants());
  }, [dispatch]);

  useEffect(() => {
    if (!tenantId && tenants.length) {
      setTenantId(String(tenants[0].id));
    }
  }, [tenantId, tenants]);

  useEffect(() => {
    if (tenantId) {
      dispatch(fetchTenantDomains(tenantId));
    }
  }, [dispatch, tenantId]);

  const handleSubmit = async (event) => {
    event.preventDefault();

    const result = await dispatch(createTenantDomain({
      id: tenantId,
      payload: form,
    }));

    if (!result.error) {
      setForm(initialDomain);
    }
  };

  return (
    <SaasPageShell
      title="Tenant Domains"
      description="Manage primary, subdomain, and custom domain identity for each school without leaving the SaaS workspace."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 5 }}>
          <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
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
                label="Domain"
                value={form.domain}
                onChange={(event) => setForm((current) => ({ ...current, domain: event.target.value }))}
              />
              <TextField
                select
                label="Domain Type"
                value={form.domain_type}
                onChange={(event) => setForm((current) => ({ ...current, domain_type: event.target.value }))}
              >
                {domainTypeOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </TextField>
              <Button type="submit" variant="contained" startIcon={<PublicOutlinedIcon />} disabled={!tenantId || saving}>
                Add Domain
              </Button>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 7 }}>
          <AppDataTable
            title="Domain Registry"
            columns={[
              { key: 'domain', header: 'Domain' },
              { key: 'domain_type', header: 'Type' },
              { key: 'status', header: 'Status' },
              { key: 'is_verified', header: 'Verified', render: (row) => (row.is_verified ? 'Yes' : 'No') },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <Button
                    size="small"
                    startIcon={<VerifiedOutlinedIcon />}
                    disabled={saving || row.is_verified}
                    onClick={() => dispatch(verifyTenantDomain(row.id))}
                  >
                    Verify
                  </Button>
                ),
              },
            ]}
            rows={selectedDomains}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No domains registered for this tenant."
          />
        </Grid>
      </Grid>
    </SaasPageShell>
  );
}
