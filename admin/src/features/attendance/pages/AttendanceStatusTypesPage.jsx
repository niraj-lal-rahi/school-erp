import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Button, Checkbox, FormControlLabel, IconButton, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { validateAttendanceRequiredFields } from '../../../utils/attendanceValidation';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { AttendanceStatusChip } from '../components/AttendanceStatusChip';
import { createAttendanceStatusType, deleteAttendanceStatusType, fetchAttendanceStatusTypes, updateAttendanceStatusType } from '../store/attendanceSlice';

const initialForm = {
  id: null,
  name: '',
  code: '',
  is_present: false,
  counts_for_attendance: true,
  color_code: '',
  description: '',
  status: 'active',
};

export function AttendanceStatusTypesPage() {
  const dispatch = useAppDispatch();
  const { statusTypes, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [formValues, setFormValues] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [validationErrors, setValidationErrors] = useState({});

  useEffect(() => {
    dispatch(fetchAttendanceStatusTypes({}));
  }, [dispatch]);

  const rows = useMemo(() => statusTypes.filter((item) => {
    const matchesSearch = `${item.name || ''} ${item.code || ''}`.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = !statusFilter || item.status === statusFilter;
    return matchesSearch && matchesStatus;
  }), [search, statusFilter, statusTypes]);

  async function handleSubmit(event) {
    event.preventDefault();
    const errors = validateAttendanceRequiredFields(formValues, [
      { key: 'name', label: 'Name', required: true },
      { key: 'code', label: 'Code', required: true },
      { key: 'status', label: 'Status', required: true },
    ]);
    setValidationErrors(errors);

    if (Object.keys(errors).length) {
      return;
    }

    const payload = {
      ...formValues,
      code: String(formValues.code).toUpperCase(),
    };

    const action = formValues.id
      ? updateAttendanceStatusType({ id: formValues.id, payload })
      : createAttendanceStatusType(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setFormValues(initialForm);
      setValidationErrors({});
      dispatch(fetchAttendanceStatusTypes({}));
    }
  }

  return (
    <AttendancePageShell
      title="Attendance Status Types"
      description="Maintain the shared attendance status catalog used across student and staff attendance, summaries, and reports."
    >
      <PermissionGate permission="attendance.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap">
              <TextField label="Name" value={formValues.name} onChange={(event) => setFormValues((current) => ({ ...current, name: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.name)} helperText={validationErrors.name} />
              <TextField label="Code" value={formValues.code} onChange={(event) => setFormValues((current) => ({ ...current, code: event.target.value.toUpperCase() }))} sx={{ minWidth: 180 }} error={Boolean(validationErrors.code)} helperText={validationErrors.code} />
              <TextField label="Color Code" value={formValues.color_code} onChange={(event) => setFormValues((current) => ({ ...current, color_code: event.target.value }))} sx={{ minWidth: 180 }} placeholder="#0b6e4f" />
              <TextField select label="Status" value={formValues.status} onChange={(event) => setFormValues((current) => ({ ...current, status: event.target.value }))} sx={{ minWidth: 180 }}>
                <MenuItem value="active">active</MenuItem>
                <MenuItem value="inactive">inactive</MenuItem>
              </TextField>
              <TextField label="Description" value={formValues.description} onChange={(event) => setFormValues((current) => ({ ...current, description: event.target.value }))} sx={{ minWidth: 260 }} />
            </Stack>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
              <FormControlLabel control={<Checkbox checked={formValues.is_present} onChange={(event) => setFormValues((current) => ({ ...current, is_present: event.target.checked }))} />} label="Counts as present" />
              <FormControlLabel control={<Checkbox checked={formValues.counts_for_attendance} onChange={(event) => setFormValues((current) => ({ ...current, counts_for_attendance: event.target.checked }))} />} label="Include in attendance percentage" />
            </Stack>
            <Stack direction="row" justifyContent="flex-end">
              <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving}>
                {formValues.id ? 'Update Status Type' : 'Save Status Type'}
              </Button>
            </Stack>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Status Type Registry"
        columns={[
          { key: 'name', header: 'Name', render: (row) => <AttendanceStatusChip status={row} /> },
          { key: 'code', header: 'Code' },
          { key: 'is_present', header: 'Present?', render: (row) => row.is_present ? 'Yes' : 'No' },
          { key: 'counts_for_attendance', header: 'Counts?', render: (row) => row.counts_for_attendance ? 'Yes' : 'No' },
          { key: 'status', header: 'Status' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <PermissionGate permission="attendance.manage" fallback={null}>
                <Stack direction="row" spacing={1}>
                  <Button size="small" onClick={() => setFormValues({
                    id: row.id,
                    name: row.name || '',
                    code: row.code || '',
                    is_present: Boolean(row.is_present),
                    counts_for_attendance: Boolean(row.counts_for_attendance),
                    color_code: row.color_code || '',
                    description: row.description || '',
                    status: row.status || 'active',
                  })}>
                    Edit
                  </Button>
                  <IconButton color="error" onClick={() => dispatch(deleteAttendanceStatusType(row.id))}>
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
            options: [
              { value: '', label: 'All' },
              { value: 'active', label: 'Active' },
              { value: 'inactive', label: 'Inactive' },
            ],
          },
        ]}
        emptyState={error || 'No attendance status types found.'}
      />
    </AttendancePageShell>
  );
}
