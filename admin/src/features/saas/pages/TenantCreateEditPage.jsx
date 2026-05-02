import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createTenant, fetchTenant, updateTenant } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';

const initialForm = {
  name: '',
  code: '',
  email: '',
  phone: '',
  domain: '',
  subdomain: '',
  city: '',
  state: '',
  country: 'India',
  postal_code: '',
  timezone: 'Asia/Kolkata',
  currency: 'INR',
  status: 'trial',
};

export function TenantCreateEditPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { tenantId } = useParams();
  const { selectedTenant, saving, loading, error } = useAppSelector((state) => state.saas);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    if (tenantId) {
      dispatch(fetchTenant(tenantId));
    }
  }, [dispatch, tenantId]);

  useEffect(() => {
    if (tenantId && selectedTenant?.id === Number(tenantId)) {
      setForm({
        ...initialForm,
        ...selectedTenant,
      });
    }
  }, [tenantId, selectedTenant]);

  const handleSubmit = async (event) => {
    event.preventDefault();

    const action = tenantId
      ? updateTenant({ id: tenantId, payload: form })
      : createTenant(form);

    const result = await dispatch(action);

    if (!result.error) {
      const nextTenantId = result.payload?.id || tenantId;
      navigate(nextTenantId ? `/saas/tenants/${nextTenantId}` : '/saas/tenants');
    }
  };

  return (
    <SaasPageShell
      title={tenantId ? 'Edit Tenant' : 'Create Tenant'}
      description="Capture the school profile, tenancy identity, and commercial status in one place."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Grid container spacing={2}>
          {[
            ['name', 'School Name'],
            ['code', 'Tenant Code'],
            ['email', 'Email'],
            ['phone', 'Phone'],
            ['domain', 'Primary Domain'],
            ['subdomain', 'Subdomain'],
            ['city', 'City'],
            ['state', 'State'],
            ['country', 'Country'],
            ['postal_code', 'Postal Code'],
          ].map(([key, label]) => (
            <Grid key={key} size={{ xs: 12, md: 6 }}>
              <TextField
                label={label}
                fullWidth
                value={form[key] || ''}
                onChange={(event) => setForm((current) => ({ ...current, [key]: event.target.value }))}
              />
            </Grid>
          ))}

          <Grid size={{ xs: 12, md: 6 }}>
            <TextField
              select
              label="Timezone"
              fullWidth
              value={form.timezone}
              onChange={(event) => setForm((current) => ({ ...current, timezone: event.target.value }))}
            >
              <MenuItem value="Asia/Kolkata">Asia/Kolkata</MenuItem>
              <MenuItem value="UTC">UTC</MenuItem>
            </TextField>
          </Grid>

          <Grid size={{ xs: 12, md: 3 }}>
            <TextField
              label="Currency"
              fullWidth
              value={form.currency}
              onChange={(event) => setForm((current) => ({ ...current, currency: event.target.value.toUpperCase() }))}
            />
          </Grid>

          <Grid size={{ xs: 12, md: 3 }}>
            <TextField
              select
              label="Status"
              fullWidth
              value={form.status}
              onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
            >
              {['trial', 'active', 'suspended', 'cancelled', 'expired'].map((value) => (
                <MenuItem key={value} value={value}>
                  {value}
                </MenuItem>
              ))}
            </TextField>
          </Grid>
        </Grid>

        <Stack direction="row" justifyContent="flex-end" mt={3}>
          <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving || loading}>
            {tenantId ? 'Save Changes' : 'Create Tenant'}
          </Button>
        </Stack>
      </Paper>
    </SaasPageShell>
  );
}
