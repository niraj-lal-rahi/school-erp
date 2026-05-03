import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Alert, Button, Chip, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useDebounce } from '../../../hooks/useDebounce';
import { usePaginatedQuery } from '../../../hooks/usePaginatedQuery';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import { usePaymentsAccess } from '../hooks/usePaymentsAccess';
import { cancelPayment, fetchPaymentTransactions } from '../store/paymentsSlice';

export function PaymentTransactionsPage() {
  const dispatch = useAppDispatch();
  const { canManage } = usePaymentsAccess();
  const { transactions, transactionsPagination, loading, saving, error } = useAppSelector((state) => state.payments);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [filters, setFilters] = useState({
    provider: '',
    payment_method: '',
    status: '',
  });
  const debouncedSearch = useDebounce(search, 350);

  useEffect(() => {
    setPage(1);
  }, [debouncedSearch, filters.payment_method, filters.provider, filters.status]);

  const { requestPage } = usePaginatedQuery({
    queryAction: fetchPaymentTransactions,
    page,
    perPage: 20,
    params: {
      ...filters,
      search: debouncedSearch || undefined,
    },
  });

  return (
    <PaymentsPageShell
      title="Payment Transactions"
      description="Watch the payment rail end to end, from initiation through verification, manual review, refunds, and reconciliation."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Transactions"
        rows={transactions}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...transactionsPagination,
          page,
          onPageChange: (nextPage) => {
            setPage(nextPage);
            requestPage(nextPage);
          },
        }}
        filters={[
          {
            key: 'provider',
            label: 'Provider',
            value: filters.provider,
            onChange: (value) => setFilters((current) => ({ ...current, provider: value })),
            options: [
              { label: 'All Providers', value: '' },
              { label: 'Razorpay', value: 'razorpay' },
              { label: 'Stripe', value: 'stripe' },
              { label: 'Manual UPI', value: 'upi_manual' },
              { label: 'Offline', value: 'offline' },
            ],
          },
          {
            key: 'payment_method',
            label: 'Method',
            value: filters.payment_method,
            onChange: (value) => setFilters((current) => ({ ...current, payment_method: value })),
            options: [
              { label: 'All Methods', value: '' },
              { label: 'UPI', value: 'upi' },
              { label: 'Card', value: 'card' },
              { label: 'Netbanking', value: 'netbanking' },
              { label: 'Wallet', value: 'wallet' },
              { label: 'Bank Transfer', value: 'bank_transfer' },
            ],
          },
          {
            key: 'status',
            label: 'Status',
            value: filters.status,
            onChange: (value) => setFilters((current) => ({ ...current, status: value })),
            options: [
              { label: 'All Statuses', value: '' },
              { label: 'Pending', value: 'pending' },
              { label: 'Initiated', value: 'initiated' },
              { label: 'Successful', value: 'successful' },
              { label: 'Failed', value: 'failed' },
              { label: 'Refunded', value: 'refunded' },
            ],
          },
        ]}
        columns={[
          { key: 'transaction_no', header: 'Transaction No' },
          { key: 'provider', header: 'Provider' },
          { key: 'payment_method', header: 'Method', render: (row) => <Chip size="small" label={row.payment_method} /> },
          { key: 'amount', header: 'Amount', render: (row) => `${row.currency} ${row.amount}` },
          { key: 'status', header: 'Status', render: (row) => <PaymentStatusChip value={row.status} /> },
          { key: 'verification_status', header: 'Verification', render: (row) => <PaymentStatusChip value={row.verification_status} /> },
          { key: 'student_id', header: 'Student ID' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button size="small" startIcon={<VisibilityOutlinedIcon />}>
                  View
                </Button>
                {canManage ? (
                  <Button
                    size="small"
                    color="error"
                    disabled={saving || ['successful', 'refunded', 'cancelled'].includes(row.status)}
                    onClick={() => dispatch(cancelPayment({ id: row.id, payload: { reason: 'Cancelled from payment desk.' } }))}
                  >
                    Cancel
                  </Button>
                ) : null}
              </Stack>
            ),
          },
        ]}
      />
    </PaymentsPageShell>
  );
}
