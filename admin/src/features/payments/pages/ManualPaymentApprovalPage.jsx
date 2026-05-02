import ThumbDownOutlinedIcon from '@mui/icons-material/ThumbDownOutlined';
import ThumbUpOutlinedIcon from '@mui/icons-material/ThumbUpOutlined';
import { Alert, Button, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { PaymentStatusChip } from '../components/PaymentStatusChip';
import { PaymentsPageShell } from '../components/PaymentsPageShell';
import { manualApprovePayment, fetchPaymentTransactions } from '../store/paymentsSlice';

export function ManualPaymentApprovalPage() {
  const dispatch = useAppDispatch();
  const { transactions, loading, saving, error } = useAppSelector((state) => state.payments);
  const [remarksById, setRemarksById] = useState({});

  useEffect(() => {
    dispatch(fetchPaymentTransactions({ verification_status: 'manual_review', per_page: 20 }));
  }, [dispatch]);

  return (
    <PaymentsPageShell
      title="Manual Payment Approval"
      description="Give accountants a focused queue for payments that need human confirmation before the ledger and the student account move forward."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 2, border: '1px solid rgba(20,33,61,0.08)' }}>
        <AppDataTable
          title="Manual Review Queue"
          rows={transactions}
          loading={loading}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No transactions are currently waiting for manual approval."
          columns={[
            { key: 'transaction_no', header: 'Transaction No' },
            { key: 'provider', header: 'Provider' },
            { key: 'amount', header: 'Amount', render: (row) => `${row.currency} ${row.amount}` },
            { key: 'upi_reference_no', header: 'UPI Ref' },
            { key: 'status', header: 'Status', render: (row) => <PaymentStatusChip value={row.status} /> },
            { key: 'verification_status', header: 'Verification', render: (row) => <PaymentStatusChip value={row.verification_status} /> },
            {
              key: 'remarks',
              header: 'Remarks',
              render: (row) => (
                <TextField
                  size="small"
                  placeholder="Optional review notes"
                  value={remarksById[row.id] || ''}
                  onChange={(event) => setRemarksById((current) => ({ ...current, [row.id]: event.target.value }))}
                />
              ),
            },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <Button
                    size="small"
                    startIcon={<ThumbUpOutlinedIcon />}
                    disabled={saving}
                    onClick={() => dispatch(manualApprovePayment({
                      id: row.id,
                      payload: {
                        status: row.provider === 'upi_manual' ? 'manually_verified' : 'successful',
                        verification_status: 'manual_review',
                        remarks: remarksById[row.id] || 'Approved from manual review queue.',
                        upi_reference_no: row.upi_reference_no || undefined,
                      },
                    }))}
                  >
                    Approve
                  </Button>
                  <Button
                    size="small"
                    color="error"
                    startIcon={<ThumbDownOutlinedIcon />}
                    disabled={saving}
                    onClick={() => dispatch(manualApprovePayment({
                      id: row.id,
                      payload: {
                        status: 'failed',
                        verification_status: 'failed',
                        remarks: remarksById[row.id] || 'Rejected from manual review queue.',
                      },
                    }))}
                  >
                    Reject
                  </Button>
                </Stack>
              ),
            },
          ]}
        />
      </Paper>
    </PaymentsPageShell>
  );
}
