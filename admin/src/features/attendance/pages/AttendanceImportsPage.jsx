import PlayArrowOutlinedIcon from '@mui/icons-material/PlayArrowOutlined';
import QueueOutlinedIcon from '@mui/icons-material/QueueOutlined';
import { Alert, Button, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { validateAttendanceRequiredFields } from '../../../utils/attendanceValidation';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { createAttendanceImport, createBiometricLog, fetchAttendanceImports, fetchAttendanceOptions, fetchBiometricLogs, processAttendanceImport, processBiometricLogs } from '../store/attendanceSlice';

const initialImportForm = {
  file_path: '',
  import_type: 'student',
  import_date: '',
};

const initialLogForm = {
  device_id: '',
  user_type: 'staff',
  user_id: '',
  log_datetime: '',
  log_type: 'check_in',
  raw_data: '',
};

export function AttendanceImportsPage() {
  const dispatch = useAppDispatch();
  const { imports, biometricLogs, options, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [importForm, setImportForm] = useState(initialImportForm);
  const [logForm, setLogForm] = useState(initialLogForm);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
    dispatch(fetchAttendanceImports({}));
    dispatch(fetchBiometricLogs({}));
  }, [dispatch]);

  const importRows = useMemo(() => imports.filter((item) => `${item.file_path || ''} ${item.status || ''}`.toLowerCase().includes(search.toLowerCase())), [imports, search]);

  async function handleCreateImport(event) {
    event.preventDefault();
    const errors = validateAttendanceRequiredFields(importForm, [
      { key: 'file_path', label: 'File Path', required: true },
      { key: 'import_type', label: 'Import Type', required: true },
      { key: 'import_date', label: 'Import Date', required: true },
    ]);

    if (Object.keys(errors).length) {
      return;
    }

    const result = await dispatch(createAttendanceImport(importForm));
    if (!result.error) {
      setImportForm(initialImportForm);
      dispatch(fetchAttendanceImports({}));
    }
  }

  async function handleCreateLog(event) {
    event.preventDefault();
    const result = await dispatch(createBiometricLog(logForm));
    if (!result.error) {
      setLogForm(initialLogForm);
      dispatch(fetchBiometricLogs({}));
    }
  }

  return (
    <AttendancePageShell
      title="Attendance Imports"
      description="Queue CSV-ready imports and biometric/device logs, then trigger background processing for high-volume attendance ingestion."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <PermissionGate permission="attendance.manage">
        <Stack spacing={3}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleCreateImport}>
              <TextField label="File Path" value={importForm.file_path} onChange={(event) => setImportForm((current) => ({ ...current, file_path: event.target.value }))} />
              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                <TextField select fullWidth label="Import Type" value={importForm.import_type} onChange={(event) => setImportForm((current) => ({ ...current, import_type: event.target.value }))}>
                  <MenuItem value="student">student</MenuItem>
                  <MenuItem value="staff">staff</MenuItem>
                </TextField>
                <TextField fullWidth label="Import Date" type="date" InputLabelProps={{ shrink: true }} value={importForm.import_date} onChange={(event) => setImportForm((current) => ({ ...current, import_date: event.target.value }))} />
              </Stack>
              <Stack direction="row" justifyContent="flex-end">
                <Button type="submit" variant="contained" startIcon={<QueueOutlinedIcon />} disabled={saving}>
                  Queue Import
                </Button>
              </Stack>
            </Stack>
          </Paper>

          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleCreateLog}>
              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                <TextField fullWidth label="Device ID" value={logForm.device_id} onChange={(event) => setLogForm((current) => ({ ...current, device_id: event.target.value }))} />
                <TextField select fullWidth label="User Type" value={logForm.user_type} onChange={(event) => setLogForm((current) => ({ ...current, user_type: event.target.value, user_id: '' }))}>
                  <MenuItem value="student">student</MenuItem>
                  <MenuItem value="staff">staff</MenuItem>
                </TextField>
                <TextField select fullWidth label="User" value={logForm.user_id} onChange={(event) => setLogForm((current) => ({ ...current, user_id: event.target.value }))}>
                  <MenuItem value="">Select</MenuItem>
                  {(logForm.user_type === 'student' ? options.students : options.staff).map((item) => (
                    <MenuItem key={item.id} value={item.id}>{item.full_name}</MenuItem>
                  ))}
                </TextField>
              </Stack>
              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                <TextField fullWidth label="Log Time" type="datetime-local" InputLabelProps={{ shrink: true }} value={logForm.log_datetime} onChange={(event) => setLogForm((current) => ({ ...current, log_datetime: event.target.value }))} />
                <TextField select fullWidth label="Log Type" value={logForm.log_type} onChange={(event) => setLogForm((current) => ({ ...current, log_type: event.target.value }))}>
                  <MenuItem value="check_in">check_in</MenuItem>
                  <MenuItem value="check_out">check_out</MenuItem>
                </TextField>
              </Stack>
              <TextField label="Raw Data" multiline minRows={3} value={logForm.raw_data} onChange={(event) => setLogForm((current) => ({ ...current, raw_data: event.target.value }))} />
              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} justifyContent="flex-end">
                <Button type="submit" variant="outlined" startIcon={<QueueOutlinedIcon />} disabled={saving}>
                  Add Biometric Log
                </Button>
                <Button variant="contained" startIcon={<PlayArrowOutlinedIcon />} onClick={() => dispatch(processBiometricLogs({}))} disabled={saving}>
                  Process Pending Logs
                </Button>
              </Stack>
            </Stack>
          </Paper>
        </Stack>
      </PermissionGate>

      <AppDataTable
        title="Attendance Import Jobs"
        columns={[
          { key: 'file_path', header: 'File Path' },
          { key: 'import_type', header: 'Type' },
          { key: 'import_date', header: 'Import Date' },
          { key: 'status', header: 'Status' },
          { key: 'total_records', header: 'Total' },
          { key: 'success_count', header: 'Success' },
          { key: 'failed_count', header: 'Failed' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <PermissionGate permission="attendance.manage" fallback={null}>
                <Button size="small" startIcon={<PlayArrowOutlinedIcon />} onClick={() => dispatch(processAttendanceImport(row.id))}>
                  Process
                </Button>
              </PermissionGate>
            ),
          },
        ]}
        rows={importRows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        emptyState="No import jobs available."
      />

      <AppDataTable
        title="Biometric Logs"
        columns={[
          { key: 'device_id', header: 'Device' },
          { key: 'user_type', header: 'User Type' },
          { key: 'user_id', header: 'User ID' },
          { key: 'log_datetime', header: 'Logged At' },
          { key: 'log_type', header: 'Type' },
          { key: 'processed', header: 'Processed', render: (row) => row.processed ? 'Yes' : 'No' },
        ]}
        rows={biometricLogs}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No biometric logs available."
      />
    </AttendancePageShell>
  );
}
