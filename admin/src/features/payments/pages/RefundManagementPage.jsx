import AutorenewOutlinedIcon from '@mui/icons-material/AutorenewOutlined';
import { Alert, Button, Grid, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import { fetchPaymentRefunds, processPaymentRefund, requestPaymentRefund } from '../store/paymentsSlice';

export function RefundManagementPage() {
  const dispatch = useAppDispatch();
  const { refunds, loading, saving, error } = useAppSelector((state) => state.payments);
  const [requestForm, setRequestForm] = useState({
    transactionId: '',
    amount: '',
    reason: '',
  });

  useEffect(() => {
    dispatch(fetchPaymentRefunds());
  }, [dispatch]);

  return (
    <PaymentsPageShell
      title="Refund Management"
      description="Control refund intent and refund execution in one place, while keeping the gateway response and the accounting trail aligned."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid item xs={12} lg={4}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <TextField label="Transaction ID" value={requestForm.transactionId} onChange={(event) => setRequestForm((current) => ({ ...current, transactionId: event.target.value }))} />
              <TextField label="Refund Amount" type="number" value={requestForm.amount} onChange={(event) => setRequestForm((current) => ({ ...current, amount: event.target.value }))} />
              <TextField multiline minRows={3} label="Reason" value={requestForm.reason} onChange={(event) => setRequestForm((current) => ({ ...current, reason: event.target.value }))} />
              <Button
                variant="contained"
                disabled={saving}
                onClick={() => dispatch(requestPaymentRefund({
                  id: requestForm.transactionId,
                  payload: {
                    amount: Number(requestForm.amount),
                    reason: requestForm.reason,
                  },
                }))}
              >
                Request Refund
              </Button>
            </Stack>
          </Paper>
        </Grid>

        <Grid item xs={12} lg={8}>
          <AppDataTable
            title="Refund Queue"
            rows={refunds}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No refund records are available yet."
            columns={[
              { key: 'refund_no', header: 'Refund No' },
              { key: 'transaction_id', header: 'Transaction ID' },
              { key: 'amount', header: 'Amount' },
              { key: 'reason', header: 'Reason' },
              { key: 'status', header: 'Status', render: (row) => <PaymentStatusChip value={row.status} /> },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <Button size="small" startIcon={<AutorenewOutlinedIcon />} disabled={saving} onClick={() => dispatch(processPaymentRefund(row.id))}>
                    Process
                  </Button>
                ),
              },
            ]}
          />
        </Grid>
      </Grid>
    </PaymentsPageShell>
  );
}
