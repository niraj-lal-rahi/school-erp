import LaunchOutlinedIcon from '@mui/icons-material/LaunchOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import { fetchPaymentGateways, fetchPaymentReferenceData, initiatePayment, initiateUpiPayment } from '../store/paymentsSlice';

const initialForm = {
  payable_type: 'school_fee',
  payable_id: '',
  student_id: '',
  tenant_subscription_id: '',
  gateway_id: '',
  provider: 'razorpay',
  payment_method: 'upi',
  amount: '',
  currency: 'INR',
  upi_vpa: '',
  payee_name: '',
};

export function InitiatePaymentPage() {
  const dispatch = useAppDispatch();
  const { gateways, referenceData, lastInitiatedPayment, loading, saving, error } = useAppSelector((state) => state.payments);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchPaymentGateways());
    dispatch(fetchPaymentReferenceData());
  }, [dispatch]);

  const availableGateways = useMemo(
    () => gateways.filter((gateway) => gateway.status === 'active'),
    [gateways],
  );

  useEffect(() => {
    if (!form.gateway_id && availableGateways.length) {
      const firstGateway = availableGateways[0];
      setForm((current) => ({
        ...current,
        gateway_id: String(firstGateway.id),
        provider: firstGateway.provider,
        payment_method: firstGateway.supports_upi ? 'upi' : (firstGateway.supports_card ? 'card' : current.payment_method),
      }));
    }
  }, [availableGateways, form.gateway_id]);

  const handleGatewayChange = (value) => {
    const gateway = availableGateways.find((item) => String(item.id) === value);

    setForm((current) => ({
      ...current,
      gateway_id: value,
      provider: gateway?.provider || current.provider,
      payment_method: gateway?.supports_upi ? 'upi' : gateway?.supports_card ? 'card' : current.payment_method,
    }));
  };

  const handleSubmit = () => {
    const payload = {
      ...form,
      payable_id: Number(form.payable_id),
      gateway_id: Number(form.gateway_id),
      amount: Number(form.amount),
      student_id: form.student_id ? Number(form.student_id) : undefined,
      tenant_subscription_id: form.tenant_subscription_id ? Number(form.tenant_subscription_id) : undefined,
      payee_name: form.payee_name || undefined,
      upi_vpa: form.upi_vpa || undefined,
    };

    if (payload.provider === 'upi_manual' || payload.payment_method === 'upi') {
      dispatch(initiateUpiPayment(payload));
      return;
    }

    dispatch(initiatePayment(payload));
  };

  return (
    <PaymentsPageShell
      title="Initiate Payment"
      description="Start a school-fee or SaaS-subscription payment with the right gateway, the right method, and enough context for later verification and reconciliation."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid item xs={12} lg={7}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Grid container spacing={2}>
                <Grid item xs={12} md={4}>
                  <TextField select fullWidth label="Payable Type" value={form.payable_type} onChange={(event) => setForm((current) => ({ ...current, payable_type: event.target.value }))}>
                    <MenuItem value="school_fee">School Fee</MenuItem>
                    <MenuItem value="saas_subscription">SaaS Subscription</MenuItem>
                    <MenuItem value="other">Other</MenuItem>
                  </TextField>
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField fullWidth label="Payable ID" value={form.payable_id} onChange={(event) => setForm((current) => ({ ...current, payable_id: event.target.value }))} />
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField fullWidth label="Tenant Subscription ID" value={form.tenant_subscription_id} onChange={(event) => setForm((current) => ({ ...current, tenant_subscription_id: event.target.value }))} />
                </Grid>
                <Grid item xs={12} md={6}>
                  <TextField
                    select
                    fullWidth
                    label="Student"
                    value={form.student_id}
                    onChange={(event) => setForm((current) => ({ ...current, student_id: event.target.value }))}
                  >
                    <MenuItem value="">None</MenuItem>
                    {referenceData.students.map((student) => (
                      <MenuItem key={student.id} value={student.id}>
                        {student.full_name || student.name || `Student #${student.id}`}
                      </MenuItem>
                    ))}
                  </TextField>
                </Grid>
                <Grid item xs={12} md={6}>
                  <TextField select fullWidth label="Gateway" value={form.gateway_id} onChange={(event) => handleGatewayChange(event.target.value)}>
                    {availableGateways.map((gateway) => (
                      <MenuItem key={gateway.id} value={gateway.id}>
                        {gateway.name} ({gateway.provider})
                      </MenuItem>
                    ))}
                  </TextField>
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField select fullWidth label="Provider" value={form.provider} onChange={(event) => setForm((current) => ({ ...current, provider: event.target.value }))}>
                    {['razorpay', 'stripe', 'upi_manual', 'offline'].map((provider) => (
                      <MenuItem key={provider} value={provider}>{provider}</MenuItem>
                    ))}
                  </TextField>
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField select fullWidth label="Method" value={form.payment_method} onChange={(event) => setForm((current) => ({ ...current, payment_method: event.target.value }))}>
                    {['upi', 'card', 'netbanking', 'wallet', 'bank_transfer', 'cash', 'other'].map((method) => (
                      <MenuItem key={method} value={method}>{method}</MenuItem>
                    ))}
                  </TextField>
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField fullWidth type="number" label="Amount" value={form.amount} onChange={(event) => setForm((current) => ({ ...current, amount: event.target.value }))} />
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField fullWidth label="Currency" value={form.currency} onChange={(event) => setForm((current) => ({ ...current, currency: event.target.value }))} />
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField fullWidth label="UPI VPA" value={form.upi_vpa} onChange={(event) => setForm((current) => ({ ...current, upi_vpa: event.target.value }))} />
                </Grid>
                <Grid item xs={12} md={4}>
                  <TextField fullWidth label="Payee Name" value={form.payee_name} onChange={(event) => setForm((current) => ({ ...current, payee_name: event.target.value }))} />
                </Grid>
              </Grid>

              <Button variant="contained" startIcon={<LaunchOutlinedIcon />} onClick={handleSubmit} disabled={saving || loading}>
                Initiate Payment
              </Button>
            </Stack>
          </Paper>
        </Grid>

        <Grid item xs={12} lg={5}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Latest Initiation</Typography>
              {lastInitiatedPayment ? (
                <>
                  <Stack direction="row" spacing={1} alignItems="center">
                    <Typography variant="body2" color="text.secondary">Status</Typography>
                    <PaymentStatusChip value={lastInitiatedPayment.transaction?.status} />
                  </Stack>
                  <Typography variant="body2">
                    Transaction: <strong>{lastInitiatedPayment.transaction?.transaction_no}</strong>
                  </Typography>
                  <Typography variant="body2">
                    Provider: <strong>{lastInitiatedPayment.transaction?.provider}</strong>
                  </Typography>
                  <Typography variant="body2">
                    Amount: <strong>{lastInitiatedPayment.transaction?.currency} {lastInitiatedPayment.transaction?.amount}</strong>
                  </Typography>
                  {lastInitiatedPayment.payload?.gateway_order_id ? (
                    <Typography variant="body2">Gateway Order ID: {lastInitiatedPayment.payload.gateway_order_id}</Typography>
                  ) : null}
                  {lastInitiatedPayment.upi_request?.qr_payload ? (
                    <Paper variant="outlined" sx={{ p: 2, bgcolor: 'rgba(20,33,61,0.02)' }}>
                      <Typography variant="subtitle2">UPI QR / Intent Payload</Typography>
                      <Typography variant="body2" sx={{ wordBreak: 'break-all', mt: 1 }}>
                        {lastInitiatedPayment.upi_request.qr_payload}
                      </Typography>
                    </Paper>
                  ) : null}
                </>
              ) : (
                <Alert severity="info">No payment has been initiated in this session yet.</Alert>
              )}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </PaymentsPageShell>
  );
}
