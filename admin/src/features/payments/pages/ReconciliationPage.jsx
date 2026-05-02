import PlaylistAddCheckOutlinedIcon from '@mui/icons-material/PlaylistAddCheckOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import { fetchPaymentReconciliations, reconcilePaymentTransaction } from '../store/paymentsSlice';

export function ReconciliationPage() {
  const dispatch = useAppDispatch();
  const { reconciliations, loading, saving, error } = useAppSelector((state) => state.payments);
  const [form, setForm] = useState({
    transactionId: '',
    source: 'manual',
    new_status: 'successful',
    remarks: '',
  });

  useEffect(() => {
    dispatch(fetchPaymentReconciliations());
  }, [dispatch]);

  return (
    <PaymentsPageShell
      title="Reconciliation"
      description="Capture every status correction with source, actor, and remark so payment fixes stay transparent and repeatable."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid item xs={12} lg={4}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <TextField label="Transaction ID" value={form.transactionId} onChange={(event) => setForm((current) => ({ ...current, transactionId: event.target.value }))} />
              <TextField select label="Source" value={form.source} onChange={(event) => setForm((current) => ({ ...current, source: event.target.value }))}>
                {['manual', 'webhook', 'gateway_api', 'bank_statement'].map((option) => (
                  <MenuItem key={option} value={option}>{option}</MenuItem>
                ))}
              </TextField>
              <TextField select label="New Status" value={form.new_status} onChange={(event) => setForm((current) => ({ ...current, new_status: event.target.value }))}>
                {['pending', 'initiated', 'successful', 'failed', 'cancelled', 'refunded', 'manually_verified'].map((option) => (
                  <MenuItem key={option} value={option}>{option}</MenuItem>
                ))}
              </TextField>
              <TextField multiline minRows={3} label="Remarks" value={form.remarks} onChange={(event) => setForm((current) => ({ ...current, remarks: event.target.value }))} />
              <Button
                variant="contained"
                startIcon={<PlaylistAddCheckOutlinedIcon />}
                disabled={saving}
                onClick={() => dispatch(reconcilePaymentTransaction({
                  id: form.transactionId,
                  payload: {
                    source: form.source,
                    new_status: form.new_status,
                    remarks: form.remarks,
                  },
                }))}
              >
                Reconcile Transaction
              </Button>
            </Stack>
          </Paper>
        </Grid>

        <Grid item xs={12} lg={8}>
          <AppDataTable
            title="Reconciliation Timeline"
            rows={reconciliations}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            emptyState="No reconciliation entries are available yet."
            columns={[
              { key: 'transaction_id', header: 'Transaction ID' },
              { key: 'source', header: 'Source' },
              { key: 'old_status', header: 'Old Status', render: (row) => row.old_status ? <PaymentStatusChip value={row.old_status} /> : 'N/A' },
              { key: 'new_status', header: 'New Status', render: (row) => <PaymentStatusChip value={row.new_status} /> },
              { key: 'remarks', header: 'Remarks' },
              { key: 'reconciled_at', header: 'Reconciled At' },
            ]}
          />
        </Grid>
      </Grid>
    </PaymentsPageShell>
  );
}
