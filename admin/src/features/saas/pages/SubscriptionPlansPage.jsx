import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { createPlan, deletePlan, fetchPlans } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';
import { planStatusOptions } from '../types/options';

const initialPlan = {
  name: '',
  code: '',
  description: '',
  price_monthly: 0,
  price_yearly: '',
  currency: 'INR',
  max_students: '',
  max_staff: '',
  max_storage_mb: '',
  status: 'active',
};

export function SubscriptionPlansPage() {
  const dispatch = useAppDispatch();
  const { plans, loading, saving, error } = useAppSelector((state) => state.saas);
  const [form, setForm] = useState(initialPlan);
  const [status, setStatus] = useState('');

  useEffect(() => {
    dispatch(fetchPlans({ status }));
  }, [dispatch, status]);

  const handleSubmit = async (event) => {
    event.preventDefault();
    const result = await dispatch(createPlan(form));

    if (!result.error) {
      setForm(initialPlan);
    }
  };

  return (
    <SaasPageShell
      title="Subscription Plans"
      description="Manage commercial packaging, student and staff caps, and the default pricing model for the SaaS business."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 5 }}>
          <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Grid container spacing={2}>
              {[
                ['name', 'Plan Name'],
                ['code', 'Plan Code'],
                ['price_monthly', 'Monthly Price'],
                ['price_yearly', 'Yearly Price'],
                ['max_students', 'Max Students'],
                ['max_staff', 'Max Staff'],
                ['max_storage_mb', 'Max Storage (MB)'],
              ].map(([key, label]) => (
                <Grid key={key} size={{ xs: 12, md: 6 }}>
                  <TextField
                    fullWidth
                    label={label}
                    value={form[key]}
                    onChange={(event) => setForm((current) => ({ ...current, [key]: event.target.value }))}
                  />
                </Grid>
              ))}
              <Grid size={{ xs: 12 }}>
                <TextField
                  fullWidth
                  multiline
                  minRows={3}
                  label="Description"
                  value={form.description}
                  onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))}
                />
              </Grid>
              <Grid size={{ xs: 12, md: 6 }}>
                <TextField
                  fullWidth
                  label="Currency"
                  value={form.currency}
                  onChange={(event) => setForm((current) => ({ ...current, currency: event.target.value.toUpperCase() }))}
                />
              </Grid>
              <Grid size={{ xs: 12, md: 6 }}>
                <TextField
                  select
                  fullWidth
                  label="Status"
                  value={form.status}
                  onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
                >
                  <MenuItem value="active">active</MenuItem>
                  <MenuItem value="inactive">inactive</MenuItem>
                </TextField>
              </Grid>
            </Grid>
            <Stack direction="row" justifyContent="flex-end" mt={3}>
              <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving}>
                Save Plan
              </Button>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 7 }}>
          <AppDataTable
            title="Plan Catalog"
            columns={[
              { key: 'name', header: 'Plan' },
              { key: 'code', header: 'Code' },
              { key: 'price_monthly', header: 'Monthly', render: (row) => `${row.currency} ${row.price_monthly}` },
              { key: 'max_students', header: 'Students', render: (row) => row.max_students ?? 'Unlimited' },
              { key: 'status', header: 'Status' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <Button
                    size="small"
                    color="error"
                    startIcon={<DeleteOutlineOutlinedIcon />}
                    onClick={() => dispatch(deletePlan(row.id))}
                  >
                    Delete
                  </Button>
                ),
              },
            ]}
            rows={plans}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            filters={[
              {
                key: 'status',
                label: 'Status',
                value: status,
                onChange: setStatus,
                options: planStatusOptions,
              },
            ]}
            emptyState="No subscription plans available."
          />
        </Grid>
      </Grid>
    </SaasPageShell>
  );
}
