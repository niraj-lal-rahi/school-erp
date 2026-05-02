import RocketLaunchOutlinedIcon from '@mui/icons-material/RocketLaunchOutlined';
import { Alert, Button, Grid, Paper, Stack, TextField } from '@mui/material';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { onboardSchool } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';

const initialPayload = {
  tenant: {
    name: '',
    code: '',
    email: '',
    domain: '',
    subdomain: '',
    timezone: 'Asia/Kolkata',
    currency: 'INR',
  },
  admin: {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: 'password123',
  },
  trial_days: 14,
};

export function SaasOnboardingPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { saving, error } = useAppSelector((state) => state.saas);
  const [payload, setPayload] = useState(initialPayload);

  const handleTenantChange = (key, value) => {
    setPayload((current) => ({
      ...current,
      tenant: {
        ...current.tenant,
        [key]: value,
      },
    }));
  };

  const handleAdminChange = (key, value) => {
    setPayload((current) => ({
      ...current,
      admin: {
        ...current.admin,
        [key]: value,
      },
    }));
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    const result = await dispatch(onboardSchool(payload));

    if (!result.error) {
      navigate(`/saas/tenants/${result.payload.tenant.id}`);
    }
  };

  return (
    <SaasPageShell
      title="Tenant Onboarding"
      description="Launch a new school with its SaaS tenant, admin identity, and starter commercial posture from one coordinated onboarding flow."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Grid container spacing={3}>
          <Grid size={{ xs: 12, lg: 6 }}>
            <Stack spacing={2}>
              <TextField label="School Name" value={payload.tenant.name} onChange={(event) => handleTenantChange('name', event.target.value)} />
              <TextField label="Tenant Code" value={payload.tenant.code} onChange={(event) => handleTenantChange('code', event.target.value)} />
              <TextField label="Email" value={payload.tenant.email} onChange={(event) => handleTenantChange('email', event.target.value)} />
              <TextField label="Domain" value={payload.tenant.domain} onChange={(event) => handleTenantChange('domain', event.target.value)} />
              <TextField label="Subdomain" value={payload.tenant.subdomain} onChange={(event) => handleTenantChange('subdomain', event.target.value)} />
            </Stack>
          </Grid>
          <Grid size={{ xs: 12, lg: 6 }}>
            <Stack spacing={2}>
              <TextField label="Admin First Name" value={payload.admin.first_name} onChange={(event) => handleAdminChange('first_name', event.target.value)} />
              <TextField label="Admin Last Name" value={payload.admin.last_name} onChange={(event) => handleAdminChange('last_name', event.target.value)} />
              <TextField label="Admin Email" value={payload.admin.email} onChange={(event) => handleAdminChange('email', event.target.value)} />
              <TextField label="Admin Phone" value={payload.admin.phone} onChange={(event) => handleAdminChange('phone', event.target.value)} />
              <TextField label="Trial Days" type="number" value={payload.trial_days} onChange={(event) => setPayload((current) => ({ ...current, trial_days: Number(event.target.value) }))} />
            </Stack>
          </Grid>
        </Grid>
        <Stack direction="row" justifyContent="flex-end" mt={3}>
          <Button type="submit" variant="contained" startIcon={<RocketLaunchOutlinedIcon />} disabled={saving}>
            Launch School
          </Button>
        </Stack>
      </Paper>
    </SaasPageShell>
  );
}
