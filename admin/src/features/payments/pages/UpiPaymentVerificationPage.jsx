import QrCode2OutlinedIcon from '@mui/icons-material/QrCode2Outlined';
import VerifiedOutlinedIcon from '@mui/icons-material/VerifiedOutlined';
import { Alert, Button, Grid, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import {
  expireUpiPayment,
  fetchPaymentTransactions,
  fetchUpiPaymentRequest,
  manualVerifyUpiPayment,
} from '../store/paymentsSlice';

export function UpiPaymentVerificationPage() {
  const dispatch = useAppDispatch();
  const { transactions, selectedUpiRequest, loading, saving, error } = useAppSelector((state) => state.payments);
  const [search, setSearch] = useState('');
  const [selectedTransactionId, setSelectedTransactionId] = useState('');
  const [referenceNo, setReferenceNo] = useState('');
  const [remarks, setRemarks] = useState('');

  useEffect(() => {
    dispatch(fetchPaymentTransactions({ payment_method: 'upi', per_page: 20 }));
  }, [dispatch]);

  useEffect(() => {
    if (selectedTransactionId) {
      dispatch(fetchUpiPaymentRequest(selectedTransactionId));
    }
  }, [dispatch, selectedTransactionId]);

  const rows = transactions.filter((transaction) => {
    if (transaction.payment_method !== 'upi') {
      return false;
    }

    if (!search) {
      return true;
    }

    const haystack = `${transaction.transaction_no} ${transaction.upi_vpa || ''} ${transaction.upi_reference_no || ''}`.toLowerCase();
    return haystack.includes(search.toLowerCase());
  });

  return (
    <PaymentsPageShell
      title="UPI Payment Verification"
      description="Handle QR-led collections, store customer VPA and reference numbers, and push manual review cases across the line without losing auditability."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid item xs={12} lg={7}>
          <AppDataTable
            title="Recent UPI Transactions"
            rows={rows}
            loading={loading}
            searchValue={search}
            onSearchChange={setSearch}
            emptyState="No UPI transactions are waiting in the current filter."
            columns={[
              { key: 'transaction_no', header: 'Transaction No' },
              { key: 'upi_vpa', header: 'Payer VPA' },
              { key: 'amount', header: 'Amount', render: (row) => `${row.currency} ${row.amount}` },
              { key: 'status', header: 'Status', render: (row) => <PaymentStatusChip value={row.status} /> },
              { key: 'verification_status', header: 'Verification', render: (row) => <PaymentStatusChip value={row.verification_status} /> },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => (
                  <Button size="small" startIcon={<QrCode2OutlinedIcon />} onClick={() => setSelectedTransactionId(String(row.id))}>
                    Inspect
                  </Button>
                ),
              },
            ]}
          />
        </Grid>

        <Grid item xs={12} lg={5}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">UPI Request Detail</Typography>
              {selectedUpiRequest ? (
                <>
                  <Typography variant="body2">Transaction ID: <strong>{selectedUpiRequest.transaction_id}</strong></Typography>
                  <Typography variant="body2">Payer VPA: <strong>{selectedUpiRequest.upi_vpa}</strong></Typography>
                  <Typography variant="body2">Payee: <strong>{selectedUpiRequest.payee_name || 'Not set'}</strong></Typography>
                  <Stack direction="row" spacing={1}>
                    <PaymentStatusChip value={selectedUpiRequest.status} />
                    <PaymentStatusChip value={selectedUpiRequest.transaction?.verification_status} />
                  </Stack>
                  <Paper variant="outlined" sx={{ p: 2, bgcolor: 'rgba(20,33,61,0.02)' }}>
                    <Typography variant="subtitle2">QR Payload</Typography>
                    <Typography variant="body2" sx={{ wordBreak: 'break-all', mt: 1 }}>
                      {selectedUpiRequest.qr_payload || 'QR payload unavailable'}
                    </Typography>
                  </Paper>
                  <TextField label="UPI Reference Number" value={referenceNo} onChange={(event) => setReferenceNo(event.target.value)} />
                  <TextField multiline minRows={3} label="Remarks" value={remarks} onChange={(event) => setRemarks(event.target.value)} />
                  <Stack direction={{ xs: 'column', sm: 'row' }} spacing={1}>
                    <Button
                      variant="contained"
                      startIcon={<VerifiedOutlinedIcon />}
                      disabled={saving}
                      onClick={() => dispatch(manualVerifyUpiPayment({
                        id: selectedUpiRequest.transaction_id,
                        payload: {
                          upi_reference_no: referenceNo,
                          remarks,
                        },
                      }))}
                    >
                      Manual Verify
                    </Button>
                    <Button variant="outlined" color="warning" disabled={saving} onClick={() => dispatch(expireUpiPayment(selectedUpiRequest.transaction_id))}>
                      Expire Request
                    </Button>
                  </Stack>
                </>
              ) : (
                <Alert severity="info">Pick a UPI transaction to inspect its QR payload and verification state.</Alert>
              )}
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </PaymentsPageShell>
  );
}
