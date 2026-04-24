import AutoFixHighOutlinedIcon from '@mui/icons-material/AutoFixHighOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
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
  createFeeInstallment,
  deleteFeeInstallment,
  fetchFeeHeads,
  fetchFeeInstallments,
  fetchStudentFeeAssignments,
  generateInstallmentsForAssignment,
  updateFeeInstallment,
} from '../store/financeSlice';

const initialForm = {
  id: null,
  student_fee_assignment_id: '',
  fee_head_id: '',
  installment_name: '',
  due_date: '',
  amount: '',
  discount_amount: '',
  fine_amount: '',
  paid_amount: '',
  status: 'pending',
};

export function FeeInstallmentsPage() {
  const dispatch = useAppDispatch();
  const {
    feeInstallments,
    feeHeads,
    studentFeeAssignments,
    installmentsPagination,
    loading,
    saving,
    error,
  } = useAppSelector((state) => state.finance);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [generateAssignmentId, setGenerateAssignmentId] = useState('');

  useEffect(() => {
    dispatch(fetchFeeHeads());
    dispatch(fetchStudentFeeAssignments({ per_page: 100 }));
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchFeeInstallments({
      page,
      search,
      status: statusFilter || undefined,
      per_page: 12,
    }));
  }, [dispatch, page, search, statusFilter]);

  const assignmentOptions = useMemo(() => studentFeeAssignments.map((assignment) => ({
    value: assignment.id,
    label: `${assignment.student?.full_name || 'Student'} - ${assignment.fee_structure?.name || 'Structure'}`,
  })), [studentFeeAssignments]);

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      student_fee_assignment_id: Number(form.student_fee_assignment_id),
      fee_head_id: Number(form.fee_head_id),
      installment_name: form.installment_name,
      due_date: form.due_date,
      amount: Number(form.amount),
      discount_amount: form.discount_amount === '' ? 0 : Number(form.discount_amount),
      fine_amount: form.fine_amount === '' ? 0 : Number(form.fine_amount),
      paid_amount: form.paid_amount === '' ? 0 : Number(form.paid_amount),
      status: form.status,
    };

    const action = form.id
      ? updateFeeInstallment({ id: form.id, payload })
      : createFeeInstallment(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchFeeInstallments({ page, search, status: statusFilter || undefined, per_page: 12 }));
    }
  }

  async function handleGenerate() {
    if (!generateAssignmentId) return;

    const result = await dispatch(generateInstallmentsForAssignment(Number(generateAssignmentId)));
    if (!result.error) {
      dispatch(fetchFeeInstallments({ page: 1, per_page: 12 }));
      setPage(1);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Stack spacing={3}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h5">Generate Installments</Typography>
              <Typography variant="body2" color="text.secondary">
                Create the installment schedule from a student fee assignment and its structure frequencies.
              </Typography>
              {error ? <Alert severity="error">{error}</Alert> : null}
              <TextField
                select
                label="Student Fee Assignment"
                value={generateAssignmentId}
                onChange={(event) => setGenerateAssignmentId(event.target.value)}
              >
                <MenuItem value="">Select</MenuItem>
                {assignmentOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
              <Button variant="outlined" startIcon={<AutoFixHighOutlinedIcon />} onClick={handleGenerate} disabled={saving || !generateAssignmentId}>
                {saving ? 'Generating...' : 'Generate Installments'}
              </Button>
            </Stack>
          </Paper>

          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleSubmit}>
              <Typography variant="h5">{form.id ? 'Edit Installment' : 'Manual Installment'}</Typography>
              <Typography variant="body2" color="text.secondary">
                Add or adjust installments manually for exceptions, one-off charges, or migrated fee records.
              </Typography>

              <TextField
                select
                label="Student Fee Assignment"
                value={form.student_fee_assignment_id}
                onChange={(event) => setForm((current) => ({ ...current, student_fee_assignment_id: event.target.value }))}
              >
                <MenuItem value="">Select</MenuItem>
                {assignmentOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>

              <TextField
                select
                label="Fee Head"
                value={form.fee_head_id}
                onChange={(event) => setForm((current) => ({ ...current, fee_head_id: event.target.value }))}
              >
                <MenuItem value="">Select</MenuItem>
                {feeHeads.map((item) => (
                  <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                ))}
              </TextField>

              <TextField label="Installment Name" value={form.installment_name} onChange={(event) => setForm((current) => ({ ...current, installment_name: event.target.value }))} />
              <TextField label="Due Date" type="date" InputLabelProps={{ shrink: true }} value={form.due_date} onChange={(event) => setForm((current) => ({ ...current, due_date: event.target.value }))} />
              <TextField label="Amount" type="number" value={form.amount} onChange={(event) => setForm((current) => ({ ...current, amount: event.target.value }))} />
              <TextField label="Discount Amount" type="number" value={form.discount_amount} onChange={(event) => setForm((current) => ({ ...current, discount_amount: event.target.value }))} />
              <TextField label="Fine Amount" type="number" value={form.fine_amount} onChange={(event) => setForm((current) => ({ ...current, fine_amount: event.target.value }))} />
              <TextField label="Paid Amount" type="number" value={form.paid_amount} onChange={(event) => setForm((current) => ({ ...current, paid_amount: event.target.value }))} />
              <TextField select label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                <MenuItem value="pending">Pending</MenuItem>
                <MenuItem value="partially_paid">Partially Paid</MenuItem>
                <MenuItem value="paid">Paid</MenuItem>
                <MenuItem value="overdue">Overdue</MenuItem>
                <MenuItem value="waived">Waived</MenuItem>
                <MenuItem value="cancelled">Cancelled</MenuItem>
              </TextField>

              <Button type="submit" variant="contained" disabled={saving}>
                {saving ? 'Saving...' : form.id ? 'Update Installment' : 'Create Installment'}
              </Button>
            </Stack>
          </Paper>
        </Stack>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Fee Installments"
          columns={[
            { key: 'installment_name', header: 'Installment' },
            { key: 'student', header: 'Student', render: (row) => row.student_fee_assignment?.student?.full_name || 'N/A' },
            { key: 'fee_head', header: 'Fee Head', render: (row) => row.fee_head?.name || 'N/A' },
            { key: 'due_date', header: 'Due Date' },
            { key: 'amount', header: 'Amount' },
            { key: 'balance_amount', header: 'Balance' },
            { key: 'status', header: 'Status' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    student_fee_assignment_id: row.student_fee_assignment_id || '',
                    fee_head_id: row.fee_head_id || '',
                    installment_name: row.installment_name || '',
                    due_date: row.due_date || '',
                    amount: row.amount || '',
                    discount_amount: row.discount_amount || '',
                    fine_amount: row.fine_amount || '',
                    paid_amount: row.paid_amount || '',
                    status: row.status || 'pending',
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteFeeInstallment(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={feeInstallments}
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
                { value: 'partially_paid', label: 'Partially Paid' },
                { value: 'paid', label: 'Paid' },
                { value: 'overdue', label: 'Overdue' },
                { value: 'waived', label: 'Waived' },
                { value: 'cancelled', label: 'Cancelled' },
              ],
            },
          ]}
          pagination={{
            page,
            totalPages: installmentsPagination.totalPages,
            onPageChange: setPage,
          }}
          emptyState="No fee installments found."
        />
      </Grid>
    </Grid>
  );
}
