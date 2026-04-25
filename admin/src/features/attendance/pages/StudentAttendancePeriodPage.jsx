import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Button, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { validateAttendanceRequiredFields } from '../../../utils/attendanceValidation';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { AttendanceStatusChip } from '../components/AttendanceStatusChip';
import { createStudentSession, fetchAttendanceOptions, fetchStudentSessions } from '../store/attendanceSlice';

const initialFilters = {
  academic_year_id: '',
  class_id: '',
  section_id: '',
};

const initialForm = {
  academic_year_id: '',
  school_class_id: '',
  section_id: '',
  attendance_date: '',
  attendance_period_id: '',
  subject_id: '',
  teacher_id: '',
};

export function StudentAttendancePeriodPage() {
  const dispatch = useAppDispatch();
  const { studentSessions, options, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [filters, setFilters] = useState(initialFilters);
  const [formValues, setFormValues] = useState(initialForm);
  const [validationErrors, setValidationErrors] = useState({});
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
  }, [dispatch]);

  useEffect(() => {
    dispatch(fetchStudentSessions({
      ...Object.fromEntries(Object.entries(filters).filter(([, value]) => value)),
      session_type: 'period',
    }));
  }, [dispatch, filters]);

  const rows = useMemo(() => studentSessions.filter((item) => {
    const haystack = `${item.school_class?.name || ''} ${item.section?.name || ''} ${item.period?.name || ''} ${item.subject?.name || ''}`.toLowerCase();
    return haystack.includes(search.toLowerCase());
  }), [search, studentSessions]);

  async function handleCreate(event) {
    event.preventDefault();
    const errors = validateAttendanceRequiredFields(formValues, [
      { key: 'academic_year_id', label: 'Academic Year', required: true },
      { key: 'school_class_id', label: 'Class', required: true },
      { key: 'section_id', label: 'Section', required: true },
      { key: 'attendance_date', label: 'Attendance Date', required: true },
      { key: 'attendance_period_id', label: 'Period', required: true },
    ]);
    setValidationErrors(errors);

    if (Object.keys(errors).length) {
      return;
    }

    const result = await dispatch(createStudentSession({
      ...formValues,
      session_type: 'period',
    }));

    if (!result.error) {
      setFormValues(initialForm);
      setValidationErrors({});
      dispatch(fetchStudentSessions({
        ...Object.fromEntries(Object.entries(filters).filter(([, value]) => value)),
        session_type: 'period',
      }));
    }
  }

  return (
    <AttendancePageShell
      title="Student Attendance - Period Wise"
      description="Create subject and period-linked attendance sessions for fine-grained classroom tracking."
    >
      <PermissionGate permission="attendance.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleCreate}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap">
              <TextField select label="Academic Year" value={formValues.academic_year_id} onChange={(event) => setFormValues((current) => ({ ...current, academic_year_id: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.academic_year_id)} helperText={validationErrors.academic_year_id}>
                <MenuItem value="">Select</MenuItem>
                {options.academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Class" value={formValues.school_class_id} onChange={(event) => setFormValues((current) => ({ ...current, school_class_id: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.school_class_id)} helperText={validationErrors.school_class_id}>
                <MenuItem value="">Select</MenuItem>
                {options.schoolClasses.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Section" value={formValues.section_id} onChange={(event) => setFormValues((current) => ({ ...current, section_id: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.section_id)} helperText={validationErrors.section_id}>
                <MenuItem value="">Select</MenuItem>
                {options.sections.filter((item) => !formValues.school_class_id || String(item.school_class_id) === String(formValues.school_class_id)).map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField label="Attendance Date" type="date" InputLabelProps={{ shrink: true }} value={formValues.attendance_date} onChange={(event) => setFormValues((current) => ({ ...current, attendance_date: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.attendance_date)} helperText={validationErrors.attendance_date} />
              <TextField select label="Period" value={formValues.attendance_period_id} onChange={(event) => setFormValues((current) => ({ ...current, attendance_period_id: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.attendance_period_id)} helperText={validationErrors.attendance_period_id}>
                <MenuItem value="">Select</MenuItem>
                {options.periods.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Subject" value={formValues.subject_id} onChange={(event) => setFormValues((current) => ({ ...current, subject_id: event.target.value }))} sx={{ minWidth: 220 }}>
                <MenuItem value="">Select</MenuItem>
                {options.subjects.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Teacher" value={formValues.teacher_id} onChange={(event) => setFormValues((current) => ({ ...current, teacher_id: event.target.value }))} sx={{ minWidth: 220 }}>
                <MenuItem value="">Select</MenuItem>
                {options.staff.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
              </TextField>
            </Stack>

            <Stack direction="row" justifyContent="flex-end">
              <Button type="submit" variant="contained" startIcon={<AddOutlinedIcon />} disabled={saving}>
                Create Period Session
              </Button>
            </Stack>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Period Attendance Sessions"
        columns={[
          { key: 'attendance_date', header: 'Date' },
          { key: 'period', header: 'Period', render: (row) => row.period?.name || 'N/A' },
          { key: 'subject', header: 'Subject', render: (row) => row.subject?.name || 'N/A' },
          { key: 'teacher', header: 'Teacher', render: (row) => row.teacher?.full_name || 'N/A' },
          { key: 'status', header: 'Status', render: (row) => <AttendanceStatusChip status={{ code: row.status?.toUpperCase(), name: row.status }} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Button component={Link} to={`/attendance/bulk-marking?sessionId=${row.id}`} size="small" startIcon={<VisibilityOutlinedIcon />}>
                Open
              </Button>
            ),
          },
        ]}
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'academic_year_id',
            label: 'Academic Year',
            value: filters.academic_year_id,
            onChange: (value) => setFilters((current) => ({ ...current, academic_year_id: value })),
            options: [{ value: '', label: 'All' }, ...options.academicYears.map((item) => ({ value: item.id, label: item.name }))],
          },
        ]}
        emptyState={error || 'No period attendance sessions found.'}
      />
    </AttendancePageShell>
  );
}
