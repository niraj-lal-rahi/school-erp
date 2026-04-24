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
  assignFeeStructureToStudent,
  createStudentFeeAssignment,
  deleteStudentFeeAssignment,
  fetchFinanceMasterData,
  fetchStudentFeeAssignments,
  updateStudentFeeAssignment,
} from '../store/financeSlice';

const initialForm = {
  id: null,
  mode: 'auto',
  student_id: '',
  academic_year_id: '',
  school_class_id: '',
  section_id: '',
  fee_structure_id: '',
  assigned_date: '',
  status: 'active',
  remarks: '',
};

export function StudentFeeAssignmentsPage() {
  const dispatch = useAppDispatch();
  const {
    studentFeeAssignments,
    students,
    academicYears,
    schoolClasses,
    sections,
    feeStructures,
    assignmentsPagination,
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
    dispatch(fetchStudentFeeAssignments({
      page,
      search,
      status: statusFilter || undefined,
      per_page: 10,
    }));
  }, [dispatch, page, search, statusFilter]);

  const sectionOptions = useMemo(
    () => sections.filter((section) => !form.school_class_id || String(section.school_class_id) === String(form.school_class_id)),
    [sections, form.school_class_id],
  );

  const feeStructureOptions = useMemo(() => feeStructures.filter((structure) => {
    const matchesAcademicYear = !form.academic_year_id || String(structure.academic_year_id) === String(form.academic_year_id);
    const matchesClass = !form.school_class_id || !structure.school_class_id || String(structure.school_class_id) === String(form.school_class_id);
    const matchesSection = !form.section_id || !structure.section_id || String(structure.section_id) === String(form.section_id);

    return matchesAcademicYear && matchesClass && matchesSection;
  }), [feeStructures, form.academic_year_id, form.school_class_id, form.section_id]);

  async function handleSubmit(event) {
    event.preventDefault();

    if (form.mode === 'auto' && !form.id) {
      const result = await dispatch(assignFeeStructureToStudent({
        studentId: Number(form.student_id),
        payload: {
          fee_structure_id: Number(form.fee_structure_id),
          assigned_date: form.assigned_date || null,
          status: form.status,
          remarks: form.remarks || null,
        },
      }));

      if (!result.error) {
        setForm(initialForm);
        dispatch(fetchStudentFeeAssignments({ page, search, status: statusFilter || undefined, per_page: 10 }));
      }

      return;
    }

    const payload = {
      student_id: Number(form.student_id),
      academic_year_id: Number(form.academic_year_id),
      school_class_id: Number(form.school_class_id),
      section_id: form.section_id ? Number(form.section_id) : null,
      fee_structure_id: Number(form.fee_structure_id),
      assigned_date: form.assigned_date,
      status: form.status,
      remarks: form.remarks || null,
    };

    const action = form.id
      ? updateStudentFeeAssignment({ id: form.id, payload })
      : createStudentFeeAssignment(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
      dispatch(fetchStudentFeeAssignments({ page, search, status: statusFilter || undefined, per_page: 10 }));
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Student Fee Assignments</Typography>
              <Typography variant="body2" color="text.secondary">
                Link students to fee structures either from their current enrollment or through a manual historical assignment.
              </Typography>
            </Stack>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField
              select
              label="Assignment Mode"
              value={form.mode}
              onChange={(event) => setForm((current) => ({ ...current, mode: event.target.value }))}
              disabled={Boolean(form.id)}
            >
              <MenuItem value="auto">Use Current Enrollment</MenuItem>
              <MenuItem value="manual">Manual Assignment</MenuItem>
            </TextField>

            <TextField
              select
              label="Student"
              value={form.student_id}
              onChange={(event) => setForm((current) => ({ ...current, student_id: event.target.value }))}
            >
              <MenuItem value="">Select</MenuItem>
              {students.map((item) => (
                <MenuItem key={item.id} value={item.id}>
                  {item.full_name} ({item.admission_no})
                </MenuItem>
              ))}
            </TextField>

            {form.mode === 'manual' || form.id ? (
              <>
                <TextField
                  select
                  label="Academic Year"
                  value={form.academic_year_id}
                  onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}
                >
                  <MenuItem value="">Select</MenuItem>
                  {academicYears.map((item) => (
                    <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                  ))}
                </TextField>

                <TextField
                  select
                  label="Class"
                  value={form.school_class_id}
                  onChange={(event) => setForm((current) => ({ ...current, school_class_id: event.target.value, section_id: '' }))}
                >
                  <MenuItem value="">Select</MenuItem>
                  {schoolClasses.map((item) => (
                    <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                  ))}
                </TextField>

                <TextField
                  select
                  label="Section"
                  value={form.section_id}
                  onChange={(event) => setForm((current) => ({ ...current, section_id: event.target.value }))}
                >
                  <MenuItem value="">None</MenuItem>
                  {sectionOptions.map((item) => (
                    <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
                  ))}
                </TextField>
              </>
            ) : null}

            <TextField
              select
              label="Fee Structure"
              value={form.fee_structure_id}
              onChange={(event) => setForm((current) => ({ ...current, fee_structure_id: event.target.value }))}
            >
              <MenuItem value="">Select</MenuItem>
              {feeStructureOptions.map((item) => (
                <MenuItem key={item.id} value={item.id}>
                  {item.name} ({item.code})
                </MenuItem>
              ))}
            </TextField>

            <TextField
              label="Assigned Date"
              type="date"
              InputLabelProps={{ shrink: true }}
              value={form.assigned_date}
              onChange={(event) => setForm((current) => ({ ...current, assigned_date: event.target.value }))}
            />

            <TextField
              select
              label="Status"
              value={form.status}
              onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
            >
              <MenuItem value="active">Active</MenuItem>
              <MenuItem value="inactive">Inactive</MenuItem>
              <MenuItem value="cancelled">Cancelled</MenuItem>
              <MenuItem value="completed">Completed</MenuItem>
            </TextField>

            <TextField
              label="Remarks"
              multiline
              minRows={3}
              value={form.remarks}
              onChange={(event) => setForm((current) => ({ ...current, remarks: event.target.value }))}
            />

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Assignment' : 'Create Assignment'}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Student Fee Assignments"
          columns={[
            { key: 'student', header: 'Student', render: (row) => row.student?.full_name || 'N/A' },
            { key: 'admission_no', header: 'Admission No', render: (row) => row.student?.admission_no || 'N/A' },
            { key: 'fee_structure', header: 'Fee Structure', render: (row) => row.fee_structure?.name || 'N/A' },
            { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || 'N/A' },
            { key: 'school_class', header: 'Class', render: (row) => row.school_class?.name || 'N/A' },
            { key: 'status', header: 'Status' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    mode: 'manual',
                    student_id: row.student_id || '',
                    academic_year_id: row.academic_year_id || '',
                    school_class_id: row.school_class_id || '',
                    section_id: row.section_id || '',
                    fee_structure_id: row.fee_structure_id || '',
                    assigned_date: row.assigned_date || '',
                    status: row.status || 'active',
                    remarks: row.remarks || '',
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteStudentFeeAssignment(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={studentFeeAssignments}
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
                { value: 'active', label: 'Active' },
                { value: 'inactive', label: 'Inactive' },
                { value: 'cancelled', label: 'Cancelled' },
                { value: 'completed', label: 'Completed' },
              ],
            },
          ]}
          pagination={{
            page,
            totalPages: assignmentsPagination.totalPages,
            onPageChange: setPage,
          }}
          emptyState="No student fee assignments found."
        />
      </Grid>
    </Grid>
  );
}
