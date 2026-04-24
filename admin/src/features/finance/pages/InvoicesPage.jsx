import CancelOutlinedIcon from '@mui/icons-material/CancelOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import PublishOutlinedIcon from '@mui/icons-material/PublishOutlined';
import {
  Alert,
  Button,
  Chip,
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
  cancelInvoice,
  createInvoice,
  deleteInvoice,
  fetchFeeInstallments,
  fetchFinanceMasterData,
  fetchInvoices,
  issueInvoice,
  updateInvoice,
} from '../store/financeSlice';

const initialForm = {
  id: null,
  student_id: '',
  academic_year_id: '',
  issue_date: '',
  due_date: '',
  notes: '',
  installment_ids: [],
};

export function InvoicesPage() {
  const dispatch = useAppDispatch();
  const {
    invoices,
    students,
    academicYears,
    feeInstallments,
    invoicesPagination,
    loading,
    saving,
    error,
  } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);

  useEffect(() => {
    dispatch(fetchFinanceMasterData());
    dispatch(fetchFeeInstallments({ per_page: 200 }));
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchInvoices({
      page,
      search,
      status: statusFilter || undefined,
      per_page: 12,
    }));
  }, [dispatch, page, search, statusFilter]);

  const availableInstallments = useMemo(() => feeInstallments.filter((installment) => {
    const studentId = installment.student_fee_assignment?.student?.id;
    const academicYearId = installment.student_fee_assignment?.academic_year?.id;
    const matchesStudent = !form.student_id || String(studentId) === String(form.student_id);
    const matchesAcademicYear = !form.academic_year_id || String(academicYearId) === String(form.academic_year_id);

    return matchesStudent && matchesAcademicYear;
  }), [feeInstallments, form.student_id, form.academic_year_id]);

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      student_id: Number(form.student_id),
      academic_year_id: Number(form.academic_year_id),
      issue_date: form.issue_date,
      due_date: form.due_date,
      notes: form.notes || null,
      installment_ids: form.installment_ids.map((value) => Number(value)),
    };

    const action = form.id
      ? updateInvoice({ id: form.id, payload })
      : createInvoice(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchInvoices({ page, search, status: statusFilter || undefined, per_page: 12 }));
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h5">{form.id ? 'Edit Invoice' : 'Create Invoice'}</Typography>
            <Typography variant="body2" color="text.secondary">
              Combine pending installments into draft invoices, then issue them for collection.
            </Typography>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField
              select
              label="Student"
              value={form.student_id}
              onChange={(event) => setForm((current) => ({ ...current, student_id: event.target.value, installment_ids: [] }))}
            >
              <MenuItem value="">Select</MenuItem>
              {students.map((item) => (
                <MenuItem key={item.id} value={item.id}>
                  {item.full_name} ({item.admission_no})
                </MenuItem>
              ))}
            </TextField>

            <TextField
              select
              label="Academic Year"
              value={form.academic_year_id}
              onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value, installment_ids: [] }))}
            >
              <MenuItem value="">Select</MenuItem>
              {academicYears.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>

            <TextField
              select
              SelectProps={{ multiple: true }}
              label="Installments"
              value={form.installment_ids}
              onChange={(event) => setForm((current) => ({
                ...current,
                installment_ids: typeof event.target.value === 'string'
                  ? event.target.value.split(',')
                  : event.target.value,
              }))}
              helperText="Choose the installments to include in this invoice."
            >
              {availableInstallments.map((item) => (
                <MenuItem key={item.id} value={item.id}>
                  {item.installment_name} - {item.amount}
                </MenuItem>
              ))}
            </TextField>

            <TextField
              label="Issue Date"
              type="date"
              InputLabelProps={{ shrink: true }}
              value={form.issue_date}
              onChange={(event) => setForm((current) => ({ ...current, issue_date: event.target.value }))}
            />
            <TextField
              label="Due Date"
              type="date"
              InputLabelProps={{ shrink: true }}
              value={form.due_date}
              onChange={(event) => setForm((current) => ({ ...current, due_date: event.target.value }))}
            />
            <TextField
              label="Notes"
              multiline
              minRows={3}
              value={form.notes}
              onChange={(event) => setForm((current) => ({ ...current, notes: event.target.value }))}
            />

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Invoice' : 'Create Invoice'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Invoices"
          columns={[
            { key: 'invoice_no', header: 'Invoice No' },
            { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
            { key: 'issue_date', header: 'Issue Date' },
            { key: 'due_date', header: 'Due Date' },
            { key: 'grand_total', header: 'Grand Total' },
            { key: 'balance_amount', header: 'Balance' },
            {
              key: 'status',
              header: 'Status',
              render: (row) => <Chip size="small" label={row.status} color={row.status === 'issued' ? 'primary' : row.status === 'cancelled' ? 'default' : 'warning'} />,
            },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    student_id: row.student_id || '',
                    academic_year_id: row.academic_year_id || '',
                    issue_date: row.issue_date || '',
                    due_date: row.due_date || '',
                    notes: row.notes || '',
                    installment_ids: (row.items || []).map((item) => item.fee_installment_id).filter(Boolean),
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="success" disabled={row.status !== 'draft'} onClick={() => dispatch(issueInvoice(row.id))}>
                    <PublishOutlinedIcon />
                  </IconButton>
                  <IconButton color="warning" disabled={row.status === 'cancelled'} onClick={() => dispatch(cancelInvoice(row.id))}>
                    <CancelOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteInvoice(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={invoices}
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
                { value: 'issued', label: 'Issued' },
                { value: 'partially_paid', label: 'Partially Paid' },
                { value: 'paid', label: 'Paid' },
                { value: 'overdue', label: 'Overdue' },
                { value: 'cancelled', label: 'Cancelled' },
              ],
            },
          ]}
          pagination={{
            page,
            totalPages: invoicesPagination.totalPages,
            onPageChange: setPage,
          }}
          emptyState="No invoices created yet."
        />
      </Grid>
    </Grid>
  );
}
