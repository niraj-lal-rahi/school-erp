import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import {
  Alert,
  Autocomplete,
  Box,
  Button,
  Divider,
  Grid,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useMemo, useState } from 'react';
import { validateStudentForm } from '../../../utils/studentValidation';
import { useAppSelector } from '../../../hooks/redux';

const defaultStudent = {
  admission_no: '',
  first_name: '',
  last_name: '',
  preferred_name: '',
  email: '',
  phone: '',
  gender: 'male',
  date_of_birth: '',
  admission_date: '',
  blood_group: '',
  status: 'active',
  medical_notes: '',
  address: {
    line1: '',
    city: '',
    state: '',
    postal_code: '',
  },
  guardians: [],
  enrollment: {
    academic_year_id: '',
    school_class_id: '',
    section_id: '',
    roll_number: '',
    status: 'active',
    joined_on: '',
  },
  admission: {
    academic_year_id: '',
    applied_class_id: '',
    status: 'applied',
    applied_on: '',
    admitted_on: '',
    remarks: '',
  },
};

export function StudentForm({
  initialValues,
  onSubmit,
  saving,
  error,
  title,
}) {
  const { guardians: guardianOptions, academicYears, classes } = useAppSelector((state) => state.masterData);
  const [values, setValues] = useState(() => ({
    ...defaultStudent,
    ...initialValues,
    address: { ...defaultStudent.address, ...(initialValues?.address || {}) },
    enrollment: { ...defaultStudent.enrollment, ...(initialValues?.enrollment || {}) },
    admission: { ...defaultStudent.admission, ...(initialValues?.admission || {}) },
    guardians: initialValues?.guardians || defaultStudent.guardians,
  }));
  const [validationErrors, setValidationErrors] = useState({});

  const guardianText = useMemo(
    () => (values.guardians || []).map((guardian) => guardian.id),
    [values.guardians]
  );

  const availableSections = useMemo(() => {
    const selectedClass = classes.find((item) => Number(item.id) === Number(values.enrollment.school_class_id));
    return selectedClass?.sections || [];
  }, [classes, values.enrollment.school_class_id]);

  function handleChange(field, nextValue) {
    setValues((current) => ({ ...current, [field]: nextValue }));
  }

  function handleNestedChange(group, field, nextValue) {
    setValues((current) => ({
      ...current,
      [group]: {
        ...current[group],
        [field]: nextValue,
      },
    }));
  }

  function handleGuardians(nextGuardians) {
    const guardians = nextGuardians.map((guardian, index) => ({
      id: guardian.id,
      relationship: guardian.relationship_type || (index === 0 ? 'Primary Guardian' : 'Guardian'),
      is_primary: index === 0,
      is_emergency_contact: index === 0,
      pickup_authorized: true,
    }));

    setValues((current) => ({ ...current, guardians }));
  }

  function submitForm(event) {
    event.preventDefault();
    const nextErrors = validateStudentForm(values);

    setValidationErrors(nextErrors);

    if (Object.keys(nextErrors).length > 0) {
      return;
    }

    onSubmit(values);
  }

  return (
    <Paper component="form" onSubmit={submitForm} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={3}>
        <Box>
          <Typography variant="h5">{title}</Typography>
          <Typography variant="body2" color="text.secondary">
            Capture identity, lifecycle, admissions, class placement, and guardian mapping in one workflow.
          </Typography>
        </Box>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Admission No" value={values.admission_no} onChange={(e) => handleChange('admission_no', e.target.value)} error={Boolean(validationErrors.admission_no)} helperText={validationErrors.admission_no} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="First Name" value={values.first_name} onChange={(e) => handleChange('first_name', e.target.value)} error={Boolean(validationErrors.first_name)} helperText={validationErrors.first_name} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Last Name" value={values.last_name} onChange={(e) => handleChange('last_name', e.target.value)} error={Boolean(validationErrors.last_name)} helperText={validationErrors.last_name} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Preferred Name" value={values.preferred_name} onChange={(e) => handleChange('preferred_name', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Email" value={values.email} onChange={(e) => handleChange('email', e.target.value)} error={Boolean(validationErrors.email)} helperText={validationErrors.email} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Phone" value={values.phone} onChange={(e) => handleChange('phone', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField select fullWidth label="Gender" value={values.gender} onChange={(e) => handleChange('gender', e.target.value)} error={Boolean(validationErrors.gender)} helperText={validationErrors.gender}>
              <MenuItem value="male">Male</MenuItem>
              <MenuItem value="female">Female</MenuItem>
              <MenuItem value="other">Other</MenuItem>
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth type="date" label="Date of Birth" value={values.date_of_birth} onChange={(e) => handleChange('date_of_birth', e.target.value)} InputLabelProps={{ shrink: true }} error={Boolean(validationErrors.date_of_birth)} helperText={validationErrors.date_of_birth} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth type="date" label="Admission Date" value={values.admission_date} onChange={(e) => handleChange('admission_date', e.target.value)} InputLabelProps={{ shrink: true }} error={Boolean(validationErrors.admission_date)} helperText={validationErrors.admission_date} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Blood Group" value={values.blood_group} onChange={(e) => handleChange('blood_group', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField select fullWidth label="Lifecycle Status" value={values.status} onChange={(e) => handleChange('status', e.target.value)} error={Boolean(validationErrors.status)} helperText={validationErrors.status}>
              <MenuItem value="active">Active</MenuItem>
              <MenuItem value="inactive">Inactive</MenuItem>
              <MenuItem value="alumni">Alumni</MenuItem>
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <Autocomplete
              multiple
              options={guardianOptions}
              getOptionLabel={(option) => `${option.first_name} ${option.last_name}`}
              value={guardianOptions.filter((option) => guardianText.includes(option.id))}
              onChange={(_, nextValue) => handleGuardians(nextValue)}
              renderInput={(params) => (
                <TextField
                  {...params}
                  label="Guardians"
                  error={Boolean(validationErrors.guardians)}
                  helperText={validationErrors.guardians || 'Choose one or more mapped guardians.'}
                />
              )}
            />
          </Grid>
          <Grid size={12}>
            <TextField fullWidth multiline minRows={3} label="Medical Notes" value={values.medical_notes} onChange={(e) => handleChange('medical_notes', e.target.value)} />
          </Grid>
        </Grid>

        <Divider />

        <Typography variant="h6">Address</Typography>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Address Line" value={values.address.line1} onChange={(e) => handleNestedChange('address', 'line1', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField fullWidth label="City" value={values.address.city} onChange={(e) => handleNestedChange('address', 'city', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField fullWidth label="State" value={values.address.state} onChange={(e) => handleNestedChange('address', 'state', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField fullWidth label="Postal Code" value={values.address.postal_code} onChange={(e) => handleNestedChange('address', 'postal_code', e.target.value)} />
          </Grid>
        </Grid>

        <Divider />

        <Typography variant="h6">Enrollment</Typography>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Academic Year" value={values.enrollment.academic_year_id} onChange={(e) => handleNestedChange('enrollment', 'academic_year_id', e.target.value)} error={Boolean(validationErrors.enrollment_academic_year_id)} helperText={validationErrors.enrollment_academic_year_id}>
              {academicYears.map((year) => (
                <MenuItem key={year.id} value={year.id}>
                  {year.name}
                </MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Class" value={values.enrollment.school_class_id} onChange={(e) => handleNestedChange('enrollment', 'school_class_id', e.target.value)} error={Boolean(validationErrors.enrollment_school_class_id)} helperText={validationErrors.enrollment_school_class_id}>
              {classes.map((schoolClass) => (
                <MenuItem key={schoolClass.id} value={schoolClass.id}>
                  {schoolClass.name} ({schoolClass.code})
                </MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField select fullWidth label="Section" value={values.enrollment.section_id} onChange={(e) => handleNestedChange('enrollment', 'section_id', e.target.value)}>
              <MenuItem value="">None</MenuItem>
              {availableSections.map((section) => (
                <MenuItem key={section.id} value={section.id}>
                  {section.name}
                </MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField fullWidth label="Roll Number" value={values.enrollment.roll_number} onChange={(e) => handleNestedChange('enrollment', 'roll_number', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField select fullWidth label="Enrollment Status" value={values.enrollment.status} onChange={(e) => handleNestedChange('enrollment', 'status', e.target.value)}>
              <MenuItem value="active">Active</MenuItem>
              <MenuItem value="inactive">Inactive</MenuItem>
              <MenuItem value="completed">Completed</MenuItem>
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth type="date" label="Joined On" value={values.enrollment.joined_on} onChange={(e) => handleNestedChange('enrollment', 'joined_on', e.target.value)} InputLabelProps={{ shrink: true }} />
          </Grid>
        </Grid>

        <Divider />

        <Typography variant="h6">Admission Process</Typography>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Academic Year" value={values.admission.academic_year_id} onChange={(e) => handleNestedChange('admission', 'academic_year_id', e.target.value)}>
              {academicYears.map((year) => (
                <MenuItem key={year.id} value={year.id}>
                  {year.name}
                </MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Applied Class" value={values.admission.applied_class_id} onChange={(e) => handleNestedChange('admission', 'applied_class_id', e.target.value)}>
              {classes.map((schoolClass) => (
                <MenuItem key={schoolClass.id} value={schoolClass.id}>
                  {schoolClass.name} ({schoolClass.code})
                </MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Admission Status" value={values.admission.status} onChange={(e) => handleNestedChange('admission', 'status', e.target.value)} error={Boolean(validationErrors.admission_status)} helperText={validationErrors.admission_status}>
              <MenuItem value="applied">Applied</MenuItem>
              <MenuItem value="reviewing">Reviewing</MenuItem>
              <MenuItem value="accepted">Accepted</MenuItem>
              <MenuItem value="rejected">Rejected</MenuItem>
              <MenuItem value="admitted">Admitted</MenuItem>
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth type="date" label="Applied On" value={values.admission.applied_on} onChange={(e) => handleNestedChange('admission', 'applied_on', e.target.value)} InputLabelProps={{ shrink: true }} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth type="date" label="Admitted On" value={values.admission.admitted_on} onChange={(e) => handleNestedChange('admission', 'admitted_on', e.target.value)} InputLabelProps={{ shrink: true }} />
          </Grid>
          <Grid size={{ xs: 12, md: 9 }}>
            <TextField fullWidth label="Remarks" value={values.admission.remarks} onChange={(e) => handleNestedChange('admission', 'remarks', e.target.value)} />
          </Grid>
        </Grid>

        <Stack direction="row" justifyContent="flex-end">
          <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving}>
            {saving ? 'Saving...' : 'Save Student'}
          </Button>
        </Stack>
      </Stack>
    </Paper>
  );
}
