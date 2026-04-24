import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { validateStaffForm } from '../../../utils/hrValidation';

const genderOptions = [
  { value: 'male', label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other', label: 'Other' },
];

const staffTypeOptions = [
  { value: 'teaching', label: 'Teaching' },
  { value: 'non_teaching', label: 'Non Teaching' },
  { value: 'admin', label: 'Admin' },
  { value: 'support', label: 'Support' },
  { value: 'driver', label: 'Driver' },
  { value: 'other', label: 'Other' },
];

const employmentTypeOptions = [
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'temporary', label: 'Temporary' },
  { value: 'intern', label: 'Intern' },
];

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'resigned', label: 'Resigned' },
  { value: 'terminated', label: 'Terminated' },
  { value: 'retired', label: 'Retired' },
  { value: 'suspended', label: 'Suspended' },
];

function selectOptions(options) {
  return [{ value: '', label: 'Select' }, ...options];
}

export function createInitialStaffForm() {
  return {
    id: null,
    employee_code: '',
    user_id: '',
    department_id: '',
    designation_id: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    gender: '',
    date_of_birth: '',
    email: '',
    phone: '',
    alternate_phone: '',
    photo_path: '',
    staff_type: '',
    employment_type: '',
    joining_date: '',
    leaving_date: '',
    current_status: 'active',
    qualification_summary: '',
    experience_years: '',
    address_line1: '',
    address_line2: '',
    city: '',
    state: '',
    country: '',
    postal_code: '',
    notes: '',
  };
}

export function normalizeStaffForForm(staff) {
  return {
    id: staff.id,
    employee_code: staff.employee_code || '',
    user_id: staff.user_id || '',
    department_id: staff.department?.id || '',
    designation_id: staff.designation?.id || '',
    first_name: staff.first_name || '',
    middle_name: staff.middle_name || '',
    last_name: staff.last_name || '',
    gender: staff.gender || '',
    date_of_birth: staff.date_of_birth || '',
    email: staff.email || '',
    phone: staff.phone || '',
    alternate_phone: staff.alternate_phone || '',
    photo_path: staff.photo_path || '',
    staff_type: staff.staff_type || '',
    employment_type: staff.employment_type || '',
    joining_date: staff.joining_date || '',
    leaving_date: staff.leaving_date || '',
    current_status: staff.current_status || 'active',
    qualification_summary: staff.qualification_summary || '',
    experience_years: staff.experience_years || '',
    address_line1: staff.address_line1 || '',
    address_line2: staff.address_line2 || '',
    city: staff.city || '',
    state: staff.state || '',
    country: staff.country || '',
    postal_code: staff.postal_code || '',
    notes: staff.notes || '',
  };
}

export function buildStaffPayload(values) {
  return {
    ...values,
    user_id: values.user_id || null,
    department_id: values.department_id || null,
    designation_id: values.designation_id || null,
    date_of_birth: values.date_of_birth || null,
    email: values.email || null,
    phone: values.phone || null,
    alternate_phone: values.alternate_phone || null,
    photo_path: values.photo_path || null,
    leaving_date: values.leaving_date || null,
    qualification_summary: values.qualification_summary || null,
    experience_years: values.experience_years === '' ? null : Number(values.experience_years),
    address_line1: values.address_line1 || null,
    address_line2: values.address_line2 || null,
    city: values.city || null,
    state: values.state || null,
    country: values.country || null,
    postal_code: values.postal_code || null,
    notes: values.notes || null,
  };
}

export function StaffForm({
  values,
  onChange,
  onSubmit,
  options,
  saving,
  error,
  submitLabel,
}) {
  const validationErrors = validateStaffForm(values);

  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack component="form" spacing={2} onSubmit={(event) => onSubmit(event, validationErrors)}>
        <Stack spacing={0.5}>
          <Typography variant="h5">{values.id ? 'Edit Staff' : 'Add Staff Member'}</Typography>
          <Typography variant="body2" color="text.secondary">
            Keep the teaching and non-teaching directory complete so attendance, leave, payroll, and academic allocations all stay in sync.
          </Typography>
        </Stack>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <Grid container spacing={2}>
          {[
            { key: 'employee_code', label: 'Employee Code', required: true },
            { key: 'first_name', label: 'First Name', required: true },
            { key: 'middle_name', label: 'Middle Name' },
            { key: 'last_name', label: 'Last Name' },
            { key: 'gender', label: 'Gender', type: 'select', options: selectOptions(genderOptions), required: true },
            { key: 'date_of_birth', label: 'Date of Birth', type: 'date' },
            { key: 'email', label: 'Email' },
            { key: 'phone', label: 'Phone' },
            { key: 'alternate_phone', label: 'Alternate Phone' },
            { key: 'staff_type', label: 'Staff Type', type: 'select', options: selectOptions(staffTypeOptions), required: true },
            { key: 'employment_type', label: 'Employment Type', type: 'select', options: selectOptions(employmentTypeOptions), required: true },
            { key: 'joining_date', label: 'Joining Date', type: 'date', required: true },
            { key: 'leaving_date', label: 'Leaving Date', type: 'date' },
            { key: 'current_status', label: 'Current Status', type: 'select', options: selectOptions(statusOptions), required: true },
            {
              key: 'department_id',
              label: 'Department',
              type: 'select',
              options: [{ value: '', label: 'Select' }, ...(options.departments || []).map((item) => ({ value: item.id, label: item.name }))],
            },
            {
              key: 'designation_id',
              label: 'Designation',
              type: 'select',
              options: [{ value: '', label: 'Select' }, ...(options.designations || []).map((item) => ({ value: item.id, label: item.name }))],
            },
            { key: 'qualification_summary', label: 'Qualification Summary' },
            { key: 'experience_years', label: 'Experience Years' },
            { key: 'address_line1', label: 'Address Line 1' },
            { key: 'address_line2', label: 'Address Line 2' },
            { key: 'city', label: 'City' },
            { key: 'state', label: 'State' },
            { key: 'country', label: 'Country' },
            { key: 'postal_code', label: 'Postal Code' },
            { key: 'photo_path', label: 'Photo Path' },
          ].map((field) => (
            <Grid key={field.key} size={{ xs: 12, md: field.key === 'notes' ? 12 : 6 }}>
              <TextField
                fullWidth
                select={field.type === 'select'}
                type={field.type === 'date' ? 'date' : 'text'}
                label={field.label}
                value={values[field.key] ?? ''}
                onChange={(event) => onChange(field.key, event.target.value)}
                InputLabelProps={field.type === 'date' ? { shrink: true } : undefined}
                error={Boolean(validationErrors[field.key])}
                helperText={validationErrors[field.key]}
              >
                {(field.options || []).map((option) => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
          ))}

          <Grid size={{ xs: 12 }}>
            <TextField
              fullWidth
              multiline
              minRows={4}
              label="Notes"
              value={values.notes ?? ''}
              onChange={(event) => onChange('notes', event.target.value)}
            />
          </Grid>
        </Grid>

        <Button type="submit" variant="contained" disabled={saving}>
          {saving ? 'Saving...' : submitLabel}
        </Button>
      </Stack>
    </Paper>
  );
}
