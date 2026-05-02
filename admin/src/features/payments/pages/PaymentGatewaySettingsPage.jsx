import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Checkbox, FormControlLabel, Grid, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import { usePaymentsAccess } from '../hooks/usePaymentsAccess';
import {
  createPaymentGateway,
  deletePaymentGateway,
  fetchPaymentGateways,
  updatePaymentGateway,
} from '../store/paymentsSlice';

const emptyForm = {
  name: '',
  code: '',
  provider: 'razorpay',
  mode: 'test',
  status: 'active',
  supports_upi: true,
  supports_card: false,
  supports_netbanking: false,
  supports_wallet: false,
  config: '{"merchant_name":"School ERP"}',
};

export function PaymentGatewaySettingsPage() {
  const dispatch = useAppDispatch();
  const { canManageGateways } = usePaymentsAccess();
  const { gateways, loading, saving, error } = useAppSelector((state) => state.payments);
  const [search, setSearch] = useState('');
  const [provider, setProvider] = useState('');
  const [editingId, setEditingId] = useState(null);
  const [form, setForm] = useState(emptyForm);

  useEffect(() => {
    dispatch(fetchPaymentGateways({ provider: provider || undefined }));
  }, [dispatch, provider]);

  const visibleRows = gateways.filter((gateway) => {
    if (!search) {
      return true;
    }

    const haystack = `${gateway.name} ${gateway.code} ${gateway.provider}`.toLowerCase();
    return haystack.includes(search.toLowerCase());
  });

  const resetForm = () => {
    setEditingId(null);
    setForm(emptyForm);
  };

  const handleSubmit = () => {
    const payload = {
      ...form,
      config: form.config ? JSON.parse(form.config) : {},
    };

    if (editingId) {
      dispatch(updatePaymentGateway({ id: editingId, payload }));
      return;
    }

    dispatch(createPaymentGateway(payload));
  };

  const startEdit = (gateway) => {
    setEditingId(gateway.id);
    setForm({
      name: gateway.name || '',
      code: gateway.code || '',
      provider: gateway.provider || 'razorpay',
      mode: gateway.mode || 'test',
      status: gateway.status || 'active',
      supports_upi: Boolean(gateway.supports_upi),
      supports_card: Boolean(gateway.supports_card),
      supports_netbanking: Boolean(gateway.supports_netbanking),
      supports_wallet: Boolean(gateway.supports_wallet),
      config: JSON.stringify(gateway.config || {}, null, 2),
    });
  };

  return (
    <PaymentsPageShell
      title="Payment Gateway Settings"
      description="Configure tenant-safe gateway profiles for Razorpay, Stripe, UPI, and offline collections without exposing raw secret values."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      {canManageGateways ? (
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack spacing={2}>
            <Grid container spacing={2}>
              <Grid item xs={12} md={6}>
                <TextField fullWidth label="Gateway Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} />
              </Grid>
              <Grid item xs={12} md={6}>
                <TextField fullWidth label="Gateway Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} />
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField select fullWidth label="Provider" value={form.provider} onChange={(event) => setForm((current) => ({ ...current, provider: event.target.value }))}>
                  {['razorpay', 'stripe', 'upi_manual', 'offline'].map((option) => (
                    <MenuItem key={option} value={option}>{option}</MenuItem>
                  ))}
                </TextField>
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField select fullWidth label="Mode" value={form.mode} onChange={(event) => setForm((current) => ({ ...current, mode: event.target.value }))}>
                  {['test', 'live'].map((option) => (
                    <MenuItem key={option} value={option}>{option}</MenuItem>
                  ))}
                </TextField>
              </Grid>
              <Grid item xs={12} md={3}>
                <TextField select fullWidth label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                  {['active', 'inactive'].map((option) => (
                    <MenuItem key={option} value={option}>{option}</MenuItem>
                  ))}
                </TextField>
              </Grid>
              <Grid item xs={12}>
                <TextField
                  fullWidth
                  multiline
                  minRows={4}
                  label="Gateway Config (JSON)"
                  value={form.config}
                  onChange={(event) => setForm((current) => ({ ...current, config: event.target.value }))}
                />
              </Grid>
            </Grid>

            <Stack direction={{ xs: 'column', md: 'row' }} spacing={1} flexWrap="wrap">
              <FormControlLabel control={<Checkbox checked={form.supports_upi} onChange={(event) => setForm((current) => ({ ...current, supports_upi: event.target.checked }))} />} label="Supports UPI" />
              <FormControlLabel control={<Checkbox checked={form.supports_card} onChange={(event) => setForm((current) => ({ ...current, supports_card: event.target.checked }))} />} label="Supports Cards" />
              <FormControlLabel control={<Checkbox checked={form.supports_netbanking} onChange={(event) => setForm((current) => ({ ...current, supports_netbanking: event.target.checked }))} />} label="Supports Netbanking" />
              <FormControlLabel control={<Checkbox checked={form.supports_wallet} onChange={(event) => setForm((current) => ({ ...current, supports_wallet: event.target.checked }))} />} label="Supports Wallets" />
            </Stack>

            <Stack direction="row" spacing={1}>
              <Button variant="contained" startIcon={<SaveOutlinedIcon />} onClick={handleSubmit} disabled={saving}>
                {editingId ? 'Update Gateway' : 'Create Gateway'}
              </Button>
              <Button variant="text" onClick={resetForm} disabled={saving}>
                Clear
              </Button>
            </Stack>
          </Stack>
        </Paper>
      ) : null}

      <AppDataTable
        title="Configured Gateways"
        rows={visibleRows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        emptyState="No gateway configuration is available for the current tenant scope."
        filters={[
          {
            key: 'provider',
            label: 'Provider',
            value: provider,
            onChange: setProvider,
            options: [
              { label: 'All Providers', value: '' },
              { label: 'Razorpay', value: 'razorpay' },
              { label: 'Stripe', value: 'stripe' },
              { label: 'Manual UPI', value: 'upi_manual' },
              { label: 'Offline', value: 'offline' },
            ],
          },
        ]}
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'code', header: 'Code' },
          { key: 'provider', header: 'Provider' },
          { key: 'mode', header: 'Mode' },
          { key: 'status', header: 'Status', render: (row) => <PaymentStatusChip value={row.status} /> },
          {
            key: 'methods',
            header: 'Methods',
            render: (row) => [
              row.supports_upi ? 'UPI' : null,
              row.supports_card ? 'Card' : null,
              row.supports_netbanking ? 'Netbanking' : null,
              row.supports_wallet ? 'Wallet' : null,
            ].filter(Boolean).join(', ') || 'None',
          },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => canManageGateways ? (
              <Stack direction="row" spacing={1}>
                <Button size="small" onClick={() => startEdit(row)}>Edit</Button>
                <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deletePaymentGateway(row.id))}>
                  Delete
                </Button>
              </Stack>
            ) : 'View only',
          },
        ]}
      />
    </PaymentsPageShell>
  );
}
