import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import PublishedWithChangesOutlinedIcon from '@mui/icons-material/PublishedWithChangesOutlined';
import {
  Alert,
  Button,
  Grid,
  IconButton,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  approveRefund,
  createRefund,
  deleteRefund,
  fetchFinanceMasterData,
  fetchPayments,
  fetchRefunds,
  processRefund,
} from '../store/financeSlice';

const initialForm = {
  payment_id: '',
  student_id: '',
  refund_date: '',
  amount: '',
  reason: '',
};

export function RefundsPage() {
  const dispatch = useAppDispatch();
  const { refunds, refundsPagination, payments, loading, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
    dispatch(fetchPayments({ per_page: 100 }));
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchRefunds({
      page,
      search,
      status: statusFilter || undefined,
      per_page: 12,
    }));
  }, [dispatch, page, search, statusFilter]);

  const paymentOptions = useMemo(
    () => payments.filter((item) => item.status === 'successful' || item.status === 'refunded'),
    [payments],
  );

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      payment_id: Number(form.payment_id),
      student_id: Number(form.student_id),
      refund_date: form.refund_date,
      amount: Number(form.amount),
      reason: form.reason,
    };

    const result = await dispatch(createRefund(payload));
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchRefunds({ page, search, status: statusFilter || undefined, per_page: 12 }));
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h5">Refund Management</Typography>
            <Typography variant="body2" color="text.secondary">
              Raise refund requests against successful payments and process them with approval flow.
            </Typography>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField
              select
              label="Payment"
              value={form.payment_id}
              onChange={(event) => {
                const nextPaymentId = event.target.value;
                const payment = paymentOptions.find((item) => String(item.id) === String(nextPaymentId));
                setForm((current) => ({
                  ...current,
                  payment_id: nextPaymentId,
                  student_id: payment?.student_id || '',
                }));
              }}
            >
              <MenuItem value="">Select</MenuItem>
              {paymentOptions.map((item) => (
                <MenuItem key={item.id} value={item.id}>
                  {item.payment_no} - {item.student?.full_name}
                </MenuItem>
              ))}
            </TextField>

            <TextField label="Student ID" value={form.student_id} disabled />
            <TextField label="Refund Date" type="date" InputLabelProps={{ shrink: true }} value={form.refund_date} onChange={(event) => setForm((current) => ({ ...current, refund_date: event.target.value }))} />
            <TextField label="Amount" type="number" value={form.amount} onChange={(event) => setForm((current) => ({ ...current, amount: event.target.value }))} />
            <TextField label="Reason" multiline minRows={3} value={form.reason} onChange={(event) => setForm((current) => ({ ...current, reason: event.target.value }))} />

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : 'Create Refund Request'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Refund Requests"
          columns={[
            { key: 'refund_no', header: 'Refund No' },
            { key: 'payment', header: 'Payment', render: (row) => row.payment?.payment_no || 'N/A' },
            { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
            { key: 'refund_date', header: 'Refund Date' },
            { key: 'amount', header: 'Amount' },
            { key: 'status', header: 'Status' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="success" disabled={row.status !== 'requested'} onClick={() => dispatch(approveRefund(row.id))}>
                    <CheckCircleOutlineOutlinedIcon />
                  </IconButton>
                  <IconButton color="primary" disabled={row.status !== 'approved'} onClick={() => dispatch(processRefund(row.id))}>
                    <PublishedWithChangesOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" disabled={!['requested', 'rejected', 'cancelled'].includes(row.status)} onClick={() => dispatch(deleteRefund(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={refunds}
          loading={loading}
          searchValue={search}
          onSearchChange={(value) => {
            setSearch(value);
            setPage(1);
          }}
          filters={[
            {
              key: 'status',
              label: 'Status',
              value: statusFilter,
              onChange: (value) => {
                setStatusFilter(value);
                setPage(1);
              },
              options: [
                { value: '', label: 'All' },
                { value: 'requested', label: 'Requested' },
                { value: 'approved', label: 'Approved' },
                { value: 'processed', label: 'Processed' },
                { value: 'rejected', label: 'Rejected' },
                { value: 'cancelled', label: 'Cancelled' },
              ],
            },
          ]}
          pagination={{
            page,
            totalPages: refundsPagination.totalPages,
            onPageChange: setPage,
          }}
          emptyState="No refund requests found."
        />
      </Grid>
    </Grid>
  );
}
