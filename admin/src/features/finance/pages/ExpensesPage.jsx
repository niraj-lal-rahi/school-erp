import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import PaidOutlinedIcon from '@mui/icons-material/PaidOutlined';
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
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  approveExpense,
  createExpense,
  deleteExpense,
  fetchFinanceMasterData,
  fetchExpenses,
  markExpensePaid,
  updateExpense,
} from '../store/financeSlice';

const initialForm = {
  id: null,
  expense_category_id: '',
  title: '',
  description: '',
  amount: '',
  expense_date: '',
  payment_method: '',
  vendor_name: '',
  reference_no: '',
};

export function ExpensesPage() {
  const dispatch = useAppDispatch();
  const { expenses, expensesPagination, expenseCategories, loading, saving, error } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchExpenses({
      page,
      search,
      status: statusFilter || undefined,
      per_page: 12,
    }));
  }, [dispatch, page, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      expense_category_id: Number(form.expense_category_id),
      title: form.title,
      description: form.description || null,
      amount: Number(form.amount),
      expense_date: form.expense_date,
      payment_method: form.payment_method || null,
      vendor_name: form.vendor_name || null,
      reference_no: form.reference_no || null,
    };

    const action = form.id
      ? updateExpense({ id: form.id, payload })
      : createExpense(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchExpenses({ page, search, status: statusFilter || undefined, per_page: 12 }));
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h5">{form.id ? 'Edit Expense' : 'Record Expense'}</Typography>
            <Typography variant="body2" color="text.secondary">
              Capture operational expenses, send them through approval, and mark them paid when settled.
            </Typography>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField select label="Category" value={form.expense_category_id} onChange={(event) => setForm((current) => ({ ...current, expense_category_id: event.target.value }))}>
              <MenuItem value="">Select</MenuItem>
              {expenseCategories.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField label="Title" value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} />
            <TextField label="Amount" type="number" value={form.amount} onChange={(event) => setForm((current) => ({ ...current, amount: event.target.value }))} />
            <TextField label="Expense Date" type="date" InputLabelProps={{ shrink: true }} value={form.expense_date} onChange={(event) => setForm((current) => ({ ...current, expense_date: event.target.value }))} />
            <TextField label="Payment Method" value={form.payment_method} onChange={(event) => setForm((current) => ({ ...current, payment_method: event.target.value }))} />
            <TextField label="Vendor Name" value={form.vendor_name} onChange={(event) => setForm((current) => ({ ...current, vendor_name: event.target.value }))} />
            <TextField label="Reference No" value={form.reference_no} onChange={(event) => setForm((current) => ({ ...current, reference_no: event.target.value }))} />
            <TextField label="Description" multiline minRows={3} value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Expense' : 'Create Expense'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Expenses"
          columns={[
            { key: 'expense_no', header: 'Expense No' },
            { key: 'title', header: 'Title' },
            { key: 'category', header: 'Category', render: (row) => row.category?.name || 'N/A' },
            { key: 'amount', header: 'Amount' },
            { key: 'expense_date', header: 'Date' },
            { key: 'status', header: 'Status' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="success" disabled={row.status !== 'draft'} onClick={() => dispatch(approveExpense(row.id))}>
                    <CheckCircleOutlineOutlinedIcon />
                  </IconButton>
                  <IconButton color="primary" disabled={row.status !== 'approved'} onClick={() => dispatch(markExpensePaid(row.id))}>
                    <PaidOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" disabled={row.status === 'paid'} onClick={() => dispatch(deleteExpense(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={expenses}
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
                { value: 'draft', label: 'Draft' },
                { value: 'approved', label: 'Approved' },
                { value: 'paid', label: 'Paid' },
                { value: 'rejected', label: 'Rejected' },
                { value: 'cancelled', label: 'Cancelled' },
              ],
            },
          ]}
          pagination={{
            page,
            totalPages: expensesPagination.totalPages,
            onPageChange: setPage,
          }}
          emptyState="No expenses recorded yet."
        />
      </Grid>
    </Grid>
  );
}
