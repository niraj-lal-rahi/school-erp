import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Button, Checkbox, FormControlLabel, IconButton, MenuItem, Paper, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { validateAttendanceRequiredFields, validateDateRange } from '../../../utils/attendanceValidation';
import { AttendancePageShell } from '../components/AttendancePageShell';
import { createHoliday, deleteHoliday, fetchAttendanceOptions, fetchHolidays, updateHoliday } from '../store/attendanceSlice';

const initialForm = {
  id: null,
  academic_year_id: '',
  title: '',
  description: '',
  start_date: '',
  end_date: '',
  applies_to: 'all',
  school_class_id: '',
  section_id: '',
  is_recurring: false,
};

export function AttendanceHolidaysPage() {
  const dispatch = useAppDispatch();
  const { holidays, options, loading, saving, error } = useAppSelector((state) => state.attendance);
  const [formValues, setFormValues] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [validationErrors, setValidationErrors] = useState({});

  useEffect(() => {
    dispatch(fetchAttendanceOptions());
    dispatch(fetchHolidays({}));
  }, [dispatch]);

  const rows = useMemo(() => holidays.filter((item) => `${item.title || ''} ${item.applies_to || ''}`.toLowerCase().includes(search.toLowerCase())), [holidays, search]);

  async function handleSubmit(event) {
    event.preventDefault();
    const errors = {
      ...validateAttendanceRequiredFields(formValues, [
        { key: 'academic_year_id', label: 'Academic Year', required: true },
        { key: 'title', label: 'Holiday Title', required: true },
        { key: 'start_date', label: 'Start Date', required: true },
        { key: 'end_date', label: 'End Date', required: true },
      ]),
      ...validateDateRange(formValues.start_date, formValues.end_date),
    };
    setValidationErrors(errors);

    if (Object.keys(errors).length) {
      return;
    }

    const action = formValues.id
      ? updateHoliday({ id: formValues.id, payload: formValues })
      : createHoliday(formValues);

    const result = await dispatch(action);
    if (!result.error) {
      setFormValues(initialForm);
      setValidationErrors({});
      dispatch(fetchHolidays({}));
    }
  }

  return (
    <AttendancePageShell
      title="Holiday Management"
      description="Define school-wide or scoped non-working days so attendance marking and reporting stay aligned with the academic calendar."
    >
      <PermissionGate permission="attendance.manage">
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap">
              <TextField select label="Academic Year" value={formValues.academic_year_id} onChange={(event) => setFormValues((current) => ({ ...current, academic_year_id: event.target.value }))} sx={{ minWidth: 220 }} error={Boolean(validationErrors.academic_year_id)} helperText={validationErrors.academic_year_id}>
                <MenuItem value="">Select</MenuItem>
                {options.academicYears.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField label="Holiday Title" value={formValues.title} onChange={(event) => setFormValues((current) => ({ ...current, title: event.target.value }))} sx={{ minWidth: 240 }} error={Boolean(validationErrors.title)} helperText={validationErrors.title} />
              <TextField label="Start Date" type="date" InputLabelProps={{ shrink: true }} value={formValues.start_date} onChange={(event) => setFormValues((current) => ({ ...current, start_date: event.target.value }))} sx={{ minWidth: 180 }} error={Boolean(validationErrors.start_date)} helperText={validationErrors.start_date} />
              <TextField label="End Date" type="date" InputLabelProps={{ shrink: true }} value={formValues.end_date} onChange={(event) => setFormValues((current) => ({ ...current, end_date: event.target.value }))} sx={{ minWidth: 180 }} error={Boolean(validationErrors.end_date)} helperText={validationErrors.end_date} />
              <TextField select label="Applies To" value={formValues.applies_to} onChange={(event) => setFormValues((current) => ({ ...current, applies_to: event.target.value }))} sx={{ minWidth: 180 }}>
                {['all', 'students', 'staff'].map((item) => <MenuItem key={item} value={item}>{item}</MenuItem>)}
              </TextField>
              <TextField select label="Class" value={formValues.school_class_id} onChange={(event) => setFormValues((current) => ({ ...current, school_class_id: event.target.value }))} sx={{ minWidth: 180 }}>
                <MenuItem value="">All</MenuItem>
                {options.schoolClasses.map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField select label="Section" value={formValues.section_id} onChange={(event) => setFormValues((current) => ({ ...current, section_id: event.target.value }))} sx={{ minWidth: 180 }}>
                <MenuItem value="">All</MenuItem>
                {options.sections.filter((item) => !formValues.school_class_id || String(item.school_class_id) === String(formValues.school_class_id)).map((item) => <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>)}
              </TextField>
              <TextField label="Description" value={formValues.description} onChange={(event) => setFormValues((current) => ({ ...current, description: event.target.value }))} sx={{ minWidth: 260 }} />
            </Stack>
            <FormControlLabel control={<Checkbox checked={formValues.is_recurring} onChange={(event) => setFormValues((current) => ({ ...current, is_recurring: event.target.checked }))} />} label="Recurring holiday" />
            <Stack direction="row" justifyContent="flex-end">
              <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving}>
                {formValues.id ? 'Update Holiday' : 'Save Holiday'}
              </Button>
            </Stack>
          </Stack>
        </Paper>
      </PermissionGate>

      <AppDataTable
        title="Holiday Calendar"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || 'N/A' },
          { key: 'start_date', header: 'Start Date' },
          { key: 'end_date', header: 'End Date' },
          { key: 'applies_to', header: 'Applies To' },
          { key: 'is_recurring', header: 'Recurring', render: (row) => row.is_recurring ? 'Yes' : 'No' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <PermissionGate permission="attendance.manage" fallback={null}>
                <Stack direction="row" spacing={1}>
                  <Button size="small" onClick={() => setFormValues({
                    id: row.id,
                    academic_year_id: row.academic_year_id || '',
                    title: row.title || '',
                    description: row.description || '',
                    start_date: row.start_date || '',
                    end_date: row.end_date || '',
                    applies_to: row.applies_to || 'all',
                    school_class_id: row.school_class_id || '',
                    section_id: row.section_id || '',
                    is_recurring: Boolean(row.is_recurring),
                  })}>
                    Edit
                  </Button>
                  <IconButton color="error" onClick={() => dispatch(deleteHoliday(row.id))}>
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
        emptyState={error || 'No holidays configured yet.'}
      />
    </AttendancePageShell>
  );
}
