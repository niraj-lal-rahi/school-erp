import CheckOutlinedIcon from '@mui/icons-material/CheckOutlined';
import CloseOutlinedIcon from '@mui/icons-material/CloseOutlined';
import SendOutlinedIcon from '@mui/icons-material/SendOutlined';
import { Button, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { validateAttendanceRequiredFields } from '../../../utils/attendanceValidation';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { AttendanceStatusChip } from '../components/AttendanceStatusChip';
import { approveCorrection, createCorrection, fetchAttendanceOptions, fetchCorrections, rejectCorrection } from '../store/attendanceSlice';

const initialForm = {
  reference_type: 'student',
  reference_id: '',
  attendance_date: '',
  old_status_id: '',
  new_status_id: '',
  reason: '',
};

export function AttendanceCorrectionsPage() {
  const dispatch = useAppDispatch();
  const { corrections, options, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [formValues, setFormValues] = useState(initialForm);
  const [statusFilter, setStatusFilter] = useState('');
  const [validationErrors, setValidationErrors] = useState({});
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
    dispatch(fetchCorrections({}));
  }, [dispatch]);

  const references = formValues.reference_type === 'staff' ? options.staff : options.students;

  const rows = useMemo(() => corrections.filter((item) => {
    const matchesStatus = !statusFilter || item.status === statusFilter;
    const matchesSearch = `${item.reference_type} ${item.reference_id} ${item.reason || ''}`.toLowerCase().includes(search.toLowerCase());
    return matchesStatus && matchesSearch;
  }), [corrections, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const errors = validateAttendanceRequiredFields(formValues, [
      { key: 'reference_type', label: 'Reference Type', required: true },
      { key: 'reference_id', label: 'Reference', required: true },
      { key: 'attendance_date', label: 'Attendance Date', required: true },
      { key: 'old_status_id', label: 'Old Status', required: true },
      { key: 'new_status_id', label: 'New Status', required: true },
      { key: 'reason', label: 'Reason', required: true },
    ]);
    setValidationErrors(errors);

    if (Object.keys(errors).length) {
      return;
    }

    const result = await dispatch(createCorrection(formValues));
    if (!result.error) {
      setFormValues(initialForm);
      setValidationErrors({});
      dispatch(fetchCorrections({}));
    }
  }

  async function handleReview(id, action) {
    const thunk = action === 'approve' ? approveCorrection : rejectCorrection;
    await dispatch(thunk({ id, payload: { review_remarks: `${action === 'approve' ? 'Approved' : 'Rejected'} from admin panel.` } }));
    dispatch(fetchCorrections({}));
  }

  return (
    <AttendancePageShell
      title="Attendance Corrections"
      description="Create auditable attendance overrides and push them through an approval flow before the underlying student or staff record is changed."
    >
      <PermissionGate permission="attendance.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap">
              <TextField select label="Reference Type" value={formValues.reference_type} onChange={(event) => setFormValues((current) => ({ ...current, reference_type: event.target.value, reference_id: '' }))} sx={{ minWidth: 180 }}>
                <MenuItem value="student">Student</MenuItem>
                <MenuItem value="staff">Staff</MenuItem>
              </TextField>
              <TextField select label="Reference" value={formValues.reference_id} onChange={(event) => setFormValues((current) => ({ ...current, reference_id: event.target.value }))} sx={{ minWidth: 240 }} error={Boolean(validationErrors.reference_id)} helperText={validationErrors.reference_id}>
                <MenuItem value="">Select</MenuItem>
                {references.map((item) => <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>)}
              </TextField>
              <TextField label="Attendance Date" type="date" InputLabelProps={{ shrink: true }} value={formValues.attendance_date} onChange={(event) => setFormValues((current) => ({ ...current, attendance_date: event.target.value }))} sx={{ minWidth: 180 }} error={Boolean(validationErrors.attendance_date)} helperText={validationErrors.attendance_date} />
              <TextField select label="Old Status" value={formValues.old_status_id} onChange={(event) => setFormValues((current) => ({ ...current, old_status_id: event.target.value }))} sx={{ minWidth: 180 }} error={Boolean(validationErrors.old_status_id)} helperText={validationErrors.old_status_id}>
                <MenuItem value="">Select</MenuItem>
                {options.statusTypes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="New Status" value={formValues.new_status_id} onChange={(event) => setFormValues((current) => ({ ...current, new_status_id: event.target.value }))} sx={{ minWidth: 180 }} error={Boolean(validationErrors.new_status_id)} helperText={validationErrors.new_status_id}>
                <MenuItem value="">Select</MenuItem>
                {options.statusTypes.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField label="Reason" value={formValues.reason} onChange={(event) => setFormValues((current) => ({ ...current, reason: event.target.value }))} sx={{ minWidth: 260 }} error={Boolean(validationErrors.reason)} helperText={validationErrors.reason} />
            </Stack>

            <Stack direction="row" justifyContent="flex-end">
              <Button type="submit" variant="contained" startIcon={<SendOutlinedIcon />} disabled={saving}>
                Submit Correction Request
              </Button>
            </Stack>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Correction Requests"
        columns={[
          { key: 'attendance_date', header: 'Date' },
          { key: 'reference_type', header: 'Reference Type' },
          { key: 'reference_id', header: 'Reference ID' },
          { key: 'old_status', header: 'Old Status', render: (row) => <AttendanceStatusChip status={row.old_status} /> },
          { key: 'new_status', header: 'New Status', render: (row) => <AttendanceStatusChip status={row.new_status} /> },
          { key: 'status', header: 'Workflow Status', render: (row) => <AttendanceStatusChip status={{ code: row.status?.toUpperCase(), name: row.status }} /> },
          { key: 'reason', header: 'Reason' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => row.status === 'pending' ? (
              <PermissionGate permission="attendance.manage" fallback={null}>
                <Stack direction="row" spacing={1}>
                  <Button size="small" color="success" startIcon={<CheckOutlinedIcon />} onClick={() => handleReview(row.id, 'approve')}>
                    Approve
                  </Button>
                  <Button size="small" color="error" startIcon={<CloseOutlinedIcon />} onClick={() => handleReview(row.id, 'reject')}>
                    Reject
                  </Button>
                </Stack>
              </PermissionGate>
            ) : 'Reviewed',
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
            options: [
              { value: '', label: 'All' },
              { value: 'pending', label: 'Pending' },
              { value: 'approved', label: 'Approved' },
              { value: 'rejected', label: 'Rejected' },
            ],
          },
        ]}
        emptyState={error || 'No correction requests found.'}
      />
    </AttendancePageShell>
  );
}
