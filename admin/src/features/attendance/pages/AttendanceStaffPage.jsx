import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Button, IconButton, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { validateAttendanceRequiredFields } from '../../../utils/attendanceValidation';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { AttendanceStatusChip } from '../components/AttendanceStatusChip';
import { createStaffRecord, deleteStaffRecord, fetchAttendanceOptions, fetchStaffRecords, updateStaffRecord } from '../store/attendanceSlice';

const initialForm = {
  id: null,
  staff_id: '',
  attendance_date: '',
  attendance_status_type_id: '',
  check_in_time: '',
  check_out_time: '',
  source: 'manual',
  remarks: '',
};

export function AttendanceStaffPage() {
  const dispatch = useAppDispatch();
  const { staffRecords, options, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [formValues, setFormValues] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [validationErrors, setValidationErrors] = useState({});

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
    dispatch(fetchStaffRecords({}));
  }, [dispatch]);

  const rows = useMemo(() => staffRecords.filter((item) => {
    const matchesSearch = `${item.staff?.full_name || ''} ${item.staff?.employee_code || ''}`.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = !statusFilter || String(item.attendance_status_type_id) === String(statusFilter);
    return matchesSearch && matchesStatus;
  }), [search, staffRecords, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const errors = validateAttendanceRequiredFields(formValues, [
      { key: 'staff_id', label: 'Staff', required: true },
      { key: 'attendance_date', label: 'Attendance Date', required: true },
      { key: 'attendance_status_type_id', label: 'Status', required: true },
    ]);
    setValidationErrors(errors);

    if (Object.keys(errors).length) {
      return;
    }

    const action = formValues.id
      ? updateStaffRecord({ id: formValues.id, payload: formValues })
      : createStaffRecord(formValues);

    const result = await dispatch(action);
    if (!result.error) {
      setFormValues(initialForm);
      setValidationErrors({});
      dispatch(fetchStaffRecords({}));
    }
  }

  return (
    <AttendancePageShell
      title="Staff Attendance Management"
      description="Manage manual, mobile, biometric, and imported staff attendance using the shared attendance status master."
    >
      <PermissionGate permission="attendance.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap">
              <TextField select label="Staff" value={formValues.staff_id} onChange={(event) => setFormValues((current) => ({ ...current, staff_id: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.staff_id)} helperText={validationErrors.staff_id}>
                <MenuItem value="">Select</MenuItem>
                {options.staff.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
              </TextField>
              <TextField label="Attendance Date" type="date" InputLabelProps={{ shrink: true }} value={formValues.attendance_date} onChange={(event) => setFormValues((current) => ({ ...current, attendance_date: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.attendance_date)} helperText={validationErrors.attendance_date} />
              <TextField select label="Status" value={formValues.attendance_status_type_id} onChange={(event) => setFormValues((current) => ({ ...current, attendance_status_type_id: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.attendance_status_type_id)} helperText={validationErrors.attendance_status_type_id}>
                <MenuItem value="">Select</MenuItem>
                {options.statusTypes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField label="Check In" type="time" InputLabelProps={{ shrink: true }} value={formValues.check_in_time} onChange={(event) => setFormValues((current) => ({ ...current, check_in_time: event.target.value }))} sx={{ minWidth: 180 }} />
              <TextField label="Check Out" type="time" InputLabelProps={{ shrink: true }} value={formValues.check_out_time} onChange={(event) => setFormValues((current) => ({ ...current, check_out_time: event.target.value }))} sx={{ minWidth: 180 }} />
              <TextField select label="Source" value={formValues.source} onChange={(event) => setFormValues((current) => ({ ...current, source: event.target.value }))} sx={{ minWidth: 180 }}>
                {['manual', 'biometric', 'mobile', 'import'].map((item) => <MenuItem key={item} value={item}>{item}</MenuItem>)}
              </TextField>
              <TextField label="Remarks" value={formValues.remarks} onChange={(event) => setFormValues((current) => ({ ...current, remarks: event.target.value }))} sx={{ minWidth: 240 }} />
            </Stack>

            <Stack direction="row" justifyContent="flex-end">
              <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving}>
                {formValues.id ? 'Update Staff Attendance' : 'Save Staff Attendance'}
              </Button>
            </Stack>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Staff Attendance Register"
        columns={[
          { key: 'attendance_date', header: 'Date' },
          { key: 'staff', header: 'Staff', render: (row) => row.staff?.full_name || 'N/A' },
          { key: 'status', header: 'Status', render: (row) => <AttendanceStatusChip status={row.attendance_status_type} /> },
          { key: 'check_in_time', header: 'Check In' },
          { key: 'check_out_time', header: 'Check Out' },
          { key: 'source', header: 'Source' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <PermissionGate permission="attendance.manage" fallback={null}>
                <Stack direction="row" spacing={1}>
                  <Button size="small" onClick={() => setFormValues({
                    id: row.id,
                    staff_id: row.staff?.id || row.staff_id || '',
                    attendance_date: row.attendance_date || '',
                    attendance_status_type_id: row.attendance_status_type_id || '',
                    check_in_time: row.check_in_time || '',
                    check_out_time: row.check_out_time || '',
                    source: row.source || 'manual',
                    remarks: row.remarks || '',
                  })}>
                    Edit
                  </Button>
                  <IconButton color="error" onClick={() => dispatch(deleteStaffRecord(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              </PermissionGate>
            ),
          },
        ]}
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'status',
            label: 'Status',
            value: statusFilter,
            onChange: setStatusFilter,
            options: [{ value: '', label: 'All' }, ...options.statusTypes.map((item) => ({ value: item.id, label: item.name }))],
          },
        ]}
        emptyState={error || 'No staff attendance records found.'}
      />
    </AttendancePageShell>
  );
}
