import SyncOutlinedIcon from '@mui/icons-material/SyncOutlined';
import { Alert, Button, Grid, LinearProgress, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchTenantUsage, fetchTenants, syncTenantUsage } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';
import { SaasStatCard } from '../components/SaasStatCard';

function toPercent(current, limit) {
  if (!limit || Number(limit) <= 0) {
    return 0;
  }

  return (Number(current || 0) / Number(limit)) * 100;
}

export function TenantUsagePage() {
  const dispatch = useAppDispatch();
  const { tenants, selectedUsage, saving, error } = useAppSelector((state) => state.saas);
  const [tenantId, setTenantId] = useState('');

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
      dispatch(fetchTenantUsage(tenantId));
    }
  }, [dispatch, tenantId]);

  const progress = useMemo(() => ({
    students: toPercent(selectedUsage?.current_students, selectedUsage?.max_students),
    staff: toPercent(selectedUsage?.current_staff, selectedUsage?.max_staff),
    storage: toPercent(selectedUsage?.current_storage_mb, selectedUsage?.max_storage_mb),
  }), [selectedUsage]);

  return (
    <SaasPageShell
      title="Tenant Usage"
      description="Watch operational consumption against contracted limits and resync the counters when tenant activity changes."
      actions={(
        <Stack direction="row" spacing={2}>
          <TextField
            select
            size="small"
            label="Tenant"
            value={tenantId}
            onChange={(event) => setTenantId(event.target.value)}
            sx={{ minWidth: 240 }}
          >
            {tenants.map((tenant) => (
              <MenuItem key={tenant.id} value={tenant.id}>
                {tenant.name}
              </MenuItem>
            ))}
          </TextField>
          <Button variant="contained" startIcon={<SyncOutlinedIcon />} disabled={!tenantId || saving} onClick={() => dispatch(syncTenantUsage(tenantId))}>
            Sync Usage
          </Button>
        </Stack>
      )}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid size={{ xs: 12, md: 4 }}>
          <SaasStatCard
            label="Students"
            value={`${selectedUsage?.current_students ?? 0} / ${selectedUsage?.max_students ?? '∞'}`}
            helper="Current student occupancy against the plan limit."
            progress={progress.students}
          />
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <SaasStatCard
            label="Staff"
            value={`${selectedUsage?.current_staff ?? 0} / ${selectedUsage?.max_staff ?? '∞'}`}
            helper="Current staff headcount against the plan limit."
            progress={progress.staff}
          />
        </Grid>
        <Grid size={{ xs: 12, md: 4 }}>
          <SaasStatCard
            label="Storage"
            value={`${selectedUsage?.current_storage_mb ?? 0} MB / ${selectedUsage?.max_storage_mb ?? '∞'} MB`}
            helper="Storage burn relative to the contracted allowance."
            progress={progress.storage}
          />
        </Grid>
      </Grid>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h5">Usage Pressure</Typography>
          {[
            ['Student Utilization', progress.students],
            ['Staff Utilization', progress.staff],
            ['Storage Utilization', progress.storage],
          ].map(([label, value]) => (
            <Stack key={label} spacing={1}>
              <Stack direction="row" justifyContent="space-between">
                <Typography variant="body2">{label}</Typography>
                <Typography variant="body2" color="text.secondary">{Number(value).toFixed(1)}%</Typography>
              </Stack>
              <LinearProgress value={Math.min(100, value)} variant="determinate" sx={{ height: 10, borderRadius: 999 }} />
            </Stack>
          ))}
        </Stack>
      </Paper>
    </SaasPageShell>
  );
}
