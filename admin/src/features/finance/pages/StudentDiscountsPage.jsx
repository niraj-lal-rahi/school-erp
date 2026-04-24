import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import CloseOutlinedIcon from '@mui/icons-material/CloseOutlined';
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
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  approveStudentDiscount,
  createStudentDiscount,
  deleteStudentDiscount,
  fetchFinanceMasterData,
  fetchStudentDiscounts,
  rejectStudentDiscount,
  updateStudentDiscount,
} from '../store/financeSlice';

const initialForm = {
  id: null,
  student_id: '',
  academic_year_id: '',
  discount_type_id: '',
  fee_head_id: '',
  discount_amount: '',
  reason: '',
};

export function StudentDiscountsPage() {
  const dispatch = useAppDispatch();
  const {
    studentDiscounts,
    studentDiscountsPagination,
    students,
    academicYears,
    discountTypes,
    feeHeads,
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
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchStudentDiscounts({
      page,
      search,
      status: statusFilter || undefined,
      per_page: 12,
    }));
  }, [dispatch, page, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      student_id: Number(form.student_id),
      academic_year_id: Number(form.academic_year_id),
      discount_type_id: Number(form.discount_type_id),
      fee_head_id: form.fee_head_id ? Number(form.fee_head_id) : null,
      discount_amount: Number(form.discount_amount),
      reason: form.reason || null,
    };

    const action = form.id
      ? updateStudentDiscount({ id: form.id, payload })
      : createStudentDiscount(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchStudentDiscounts({ page, search, status: statusFilter || undefined, per_page: 12 }));
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h5">{form.id ? 'Edit Student Discount' : 'Assign Student Discount'}</Typography>
            <Typography variant="body2" color="text.secondary">
              Approve and track scholarships or concessions for individual students.
            </Typography>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField select label="Student" value={form.student_id} onChange={(event) => setForm((current) => ({ ...current, student_id: event.target.value }))}>
              <MenuItem value="">Select</MenuItem>
              {students.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Academic Year" value={form.academic_year_id} onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}>
              <MenuItem value="">Select</MenuItem>
              {academicYears.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Discount Type" value={form.discount_type_id} onChange={(event) => setForm((current) => ({ ...current, discount_type_id: event.target.value }))}>
              <MenuItem value="">Select</MenuItem>
              {discountTypes.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField select label="Fee Head" value={form.fee_head_id} onChange={(event) => setForm((current) => ({ ...current, fee_head_id: event.target.value }))}>
              <MenuItem value="">All Fee Heads</MenuItem>
              {feeHeads.map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
            </TextField>
            <TextField label="Discount Amount" type="number" value={form.discount_amount} onChange={(event) => setForm((current) => ({ ...current, discount_amount: event.target.value }))} />
            <TextField label="Reason" multiline minRows={3} value={form.reason} onChange={(event) => setForm((current) => ({ ...current, reason: event.target.value }))} />

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Student Discount' : 'Create Student Discount'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Student Discounts"
          columns={[
            { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
            { key: 'discount_type_item', header: 'Discount Type', render: (row) => row.discount_type_item?.name || 'N/A' },
            { key: 'fee_head', header: 'Fee Head', render: (row) => row.fee_head?.name || 'All Heads' },
            { key: 'discount_amount', header: 'Amount' },
            { key: 'status', header: 'Status' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    student_id: row.student_id || '',
                    academic_year_id: row.academic_year_id || '',
                    discount_type_id: row.discount_type_id || '',
                    fee_head_id: row.fee_head_id || '',
                    discount_amount: row.discount_amount || '',
                    reason: row.reason || '',
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="success" disabled={row.status !== 'pending'} onClick={() => dispatch(approveStudentDiscount(row.id))}>
                    <CheckCircleOutlineOutlinedIcon />
                  </IconButton>
                  <IconButton color="warning" disabled={row.status !== 'pending'} onClick={() => dispatch(rejectStudentDiscount(row.id))}>
                    <CloseOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteStudentDiscount(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={studentDiscounts}
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
                { value: 'approved', label: 'Approved' },
                { value: 'rejected', label: 'Rejected' },
                { value: 'cancelled', label: 'Cancelled' },
              ],
            },
          ]}
          pagination={{
            page,
            totalPages: studentDiscountsPagination.totalPages,
            onPageChange: setPage,
          }}
          emptyState="No student discounts found."
        />
      </Grid>
    </Grid>
  );
}
