import { Alert, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import { fetchFailedTransactionsReport, fetchPaymentSummary, fetchUpiPaymentsReport } from '../store/paymentsSlice';

function SummaryCard({ label, value }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Typography variant="body2" color="text.secondary">{label}</Typography>
      <Typography variant="h5" sx={{ mt: 1 }}>{value}</Typography>
    </Paper>
  );
}

export function PaymentReportsPage() {
  const dispatch = useAppDispatch();
  const { reports, loading, error } = useAppSelector((state) => state.payments);

  useEffect(() => {
    dispatch(fetchPaymentSummary());
    dispatch(fetchUpiPaymentsReport());
    dispatch(fetchFailedTransactionsReport());
  }, [dispatch]);

  return (
    <PaymentsPageShell
      title="Payment Reports"
      description="Track collection velocity, isolate failure pockets, and keep UPI review volume visible without bouncing between finance and SaaS screens."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={2}>
        <Grid item xs={12} md={4}>
          <SummaryCard label="Total Transactions" value={reports.summary?.total_transactions ?? 0} />
        </Grid>
        <Grid item xs={12} md={4}>
          <SummaryCard label="Successful Amount" value={`${reports.summary?.successful_amount ?? 0}`} />
        </Grid>
        <Grid item xs={12} md={4}>
          <SummaryCard label="Pending Transactions" value={reports.summary?.pending_transactions ?? 0} />
        </Grid>
      </Grid>

      <AppDataTable
        title="UPI Payment Queue"
        rows={reports.upiPayments}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No UPI report rows available."
        columns={[
          { key: 'transaction_id', header: 'Transaction ID' },
          { key: 'upi_vpa', header: 'UPI VPA' },
          { key: 'amount', header: 'Amount', render: (row) => `${row.currency} ${row.amount}` },
          { key: 'status', header: 'Status', render: (row) => <PaymentStatusChip value={row.status} /> },
          { key: 'expires_at', header: 'Expires At' },
        ]}
      />

      <AppDataTable
        title="Failed Transactions"
        rows={reports.failedTransactions}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No failed transactions are in the current report view."
        columns={[
          { key: 'transaction_no', header: 'Transaction No' },
          { key: 'provider', header: 'Provider' },
          { key: 'amount', header: 'Amount', render: (row) => `${row.currency} ${row.amount}` },
          { key: 'status', header: 'Status', render: (row) => <PaymentStatusChip value={row.status} /> },
          { key: 'failure_reason', header: 'Failure Reason' },
        ]}
      />
    </PaymentsPageShell>
  );
}
