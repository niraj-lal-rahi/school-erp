import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import ErrorOutlineOutlinedIcon from '@mui/icons-material/ErrorOutlineOutlined';
import { Alert, Button, Chip, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchTenantBilling, fetchTenants, markBillingFailed, markBillingPaid } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';

export function TenantBillingPage() {
  const dispatch = useAppDispatch();
  const { tenants, selectedBilling, loading, saving, error } = useAppSelector((state) => state.saas);
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
      dispatch(fetchTenantBilling(tenantId));
    }
  }, [dispatch, tenantId]);

  return (
    <SaasPageShell
      title="Tenant Billing"
      description="Track invoices, payment timing, and collection risk before a subscription falls behind."
      actions={(
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
      )}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Billing Records"
        columns={[
          { key: 'invoice_no', header: 'Invoice' },
          { key: 'amount', header: 'Amount', render: (row) => `${row.currency} ${row.amount}` },
          { key: 'billing_cycle', header: 'Cycle' },
          { key: 'billing_date', header: 'Billing Date' },
          { key: 'due_date', header: 'Due Date' },
          { key: 'status', header: 'Status', render: (row) => <Chip size="small" label={row.status} color={row.status === 'paid' ? 'success' : row.status === 'pending' ? 'warning' : 'default'} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button size="small" startIcon={<CheckCircleOutlineOutlinedIcon />} disabled={saving} onClick={() => dispatch(markBillingPaid(row.id))}>
                  Paid
                </Button>
                <Button size="small" color="error" startIcon={<ErrorOutlineOutlinedIcon />} disabled={saving} onClick={() => dispatch(markBillingFailed(row.id))}>
                  Failed
                </Button>
              </Stack>
            ),
          },
        ]}
        rows={selectedBilling}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No billing records found for this tenant."
      />
    </SaasPageShell>
  );
}
