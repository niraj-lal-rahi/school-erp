import CancelOutlinedIcon from '@mui/icons-material/CancelOutlined';
import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import {
  IconButton,
  Stack,
} from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { confirmPayment, deletePayment, failPayment, fetchPayments } from '../store/financeSlice';

export function PaymentsPage() {
  const dispatch = useAppDispatch();
  const { payments, paymentsPagination, loading } = useAppSelector((state) => state.finance);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchPayments({
      page,
      search,
      status: statusFilter || undefined,
      per_page: 12,
    }));
  }, [dispatch, page, search, statusFilter]);

  return (
    <AppDataTable
      title="Payment History"
      columns={[
        { key: 'payment_no', header: 'Payment No' },
        { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
        { key: 'invoice', header: 'Invoice', render: (row) => row.invoice?.invoice_no || 'N/A' },
        { key: 'payment_date', header: 'Date' },
        { key: 'payment_method', header: 'Method' },
        { key: 'amount', header: 'Amount' },
        { key: 'status', header: 'Status' },
        { key: 'receipt', header: 'Receipt', render: (row) => row.receipt?.receipt_no || 'Pending' },
        {
          key: 'actions',
          header: 'Actions',
          render: (row) => (
            <Stack direction="row" spacing={1}>
              <IconButton
                color="success"
                disabled={row.status !== 'pending'}
                onClick={() => dispatch(confirmPayment({ id: row.id, payload: {} }))}
              >
                <CheckCircleOutlineOutlinedIcon />
              </IconButton>
              <IconButton
                color="warning"
                disabled={row.status !== 'pending'}
                onClick={() => dispatch(failPayment({ id: row.id, payload: {} }))}
              >
                <CancelOutlinedIcon />
              </IconButton>
              <IconButton
                color="error"
                disabled={row.status === 'successful'}
                onClick={() => dispatch(deletePayment(row.id))}
              >
                <DeleteOutlineOutlinedIcon />
              </IconButton>
            </Stack>
          ),
        },
      ]}
      rows={payments}
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
            { value: 'pending', label: 'Pending' },
            { value: 'successful', label: 'Successful' },
            { value: 'failed', label: 'Failed' },
            { value: 'refunded', label: 'Refunded' },
            { value: 'cancelled', label: 'Cancelled' },
          ],
        },
      ]}
      pagination={{
        page,
        totalPages: paymentsPagination.totalPages,
        onPageChange: setPage,
      }}
      emptyState="No payments found."
    />
  );
}
