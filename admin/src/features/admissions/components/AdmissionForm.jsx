import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import {
  Alert,
  Button,
  Grid,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useMemo, useState } from 'react';
import { useAppSelector } from '../../../hooks/redux';

const defaultAdmission = {
  application_no: '',
  academic_year_id: '',
  applied_class_id: '',
  section_id: '',
  first_name: '',
  middle_name: '',
  last_name: '',
  gender: 'male',
  date_of_birth: '',
  guardian_name: '',
  guardian_phone: '',
  guardian_email: '',
  address_line1: '',
  city: '',
  state: '',
  country: 'India',
  postal_code: '',
  previous_school: '',
  remarks: '',
  application_status: 'draft',
};

export function AdmissionForm({ initialValues, onSubmit, saving, error, title }) {
  const { academicYears, classes } = useAppSelector((state) => state.masterData);
  const [values, setValues] = useState(() => ({ ...defaultAdmission, ...initialValues }));

  const sections = useMemo(() => {
    const schoolClass = classes.find((item) => Number(item.id) === Number(values.applied_class_id));
    return schoolClass?.sections || [];
  }, [classes, values.applied_class_id]);

  function handleChange(key, value) {
    setValues((current) => ({ ...current, [key]: value }));
  }

  function handleSubmit(event) {
    event.preventDefault();
    onSubmit(values);
  }

  return (
    <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={3}>
        <div>
          <Typography variant="h5">{title}</Typography>
          <Typography variant="body2" color="text.secondary">
            Register applicant records before admission approval and conversion.
          </Typography>
        </div>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth label="Application No" value={values.application_no} onChange={(e) => handleChange('application_no', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Academic Year" value={values.academic_year_id} onChange={(e) => handleChange('academic_year_id', e.target.value)}>
              {academicYears.map((year) => (
                <MenuItem key={year.id} value={year.id}>{year.name}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Applied Class" value={values.applied_class_id} onChange={(e) => handleChange('applied_class_id', e.target.value)}>
              {classes.map((schoolClass) => (
                <MenuItem key={schoolClass.id} value={schoolClass.id}>{schoolClass.name}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Section" value={values.section_id} onChange={(e) => handleChange('section_id', e.target.value)}>
              <MenuItem value="">None</MenuItem>
              {sections.map((section) => (
                <MenuItem key={section.id} value={section.id}>{section.name}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="First Name" value={values.first_name} onChange={(e) => handleChange('first_name', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Middle Name" value={values.middle_name} onChange={(e) => handleChange('middle_name', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Last Name" value={values.last_name} onChange={(e) => handleChange('last_name', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Gender" value={values.gender} onChange={(e) => handleChange('gender', e.target.value)}>
              <MenuItem value="male">Male</MenuItem>
              <MenuItem value="female">Female</MenuItem>
              <MenuItem value="other">Other</MenuItem>
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth type="date" label="Date of Birth" value={values.date_of_birth} onChange={(e) => handleChange('date_of_birth', e.target.value)} InputLabelProps={{ shrink: true }} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth label="Guardian Name" value={values.guardian_name} onChange={(e) => handleChange('guardian_name', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField fullWidth label="Guardian Phone" value={values.guardian_phone} onChange={(e) => handleChange('guardian_phone', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Guardian Email" value={values.guardian_email} onChange={(e) => handleChange('guardian_email', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Address Line 1" value={values.address_line1} onChange={(e) => handleChange('address_line1', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField fullWidth label="City" value={values.city} onChange={(e) => handleChange('city', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField fullWidth label="State" value={values.state} onChange={(e) => handleChange('state', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 2 }}>
            <TextField fullWidth label="Postal Code" value={values.postal_code} onChange={(e) => handleChange('postal_code', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Previous School" value={values.previous_school} onChange={(e) => handleChange('previous_school', e.target.value)} />
          </Grid>
          <Grid size={{ xs: 12, md: 3 }}>
            <TextField select fullWidth label="Application Status" value={values.application_status} onChange={(e) => handleChange('application_status', e.target.value)}>
              <MenuItem value="draft">Draft</MenuItem>
              <MenuItem value="submitted">Submitted</MenuItem>
              <MenuItem value="under_review">Under Review</MenuItem>
              <MenuItem value="approved">Approved</MenuItem>
              <MenuItem value="rejected">Rejected</MenuItem>
              <MenuItem value="waitlisted">Waitlisted</MenuItem>
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 12 }}>
            <TextField fullWidth multiline minRows={3} label="Remarks" value={values.remarks} onChange={(e) => handleChange('remarks', e.target.value)} />
          </Grid>
        </Grid>

        <Stack direction="row" justifyContent="flex-end">
          <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving}>
            {saving ? 'Saving...' : 'Save Application'}
          </Button>
        </Stack>
      </Stack>
    </Paper>
  );
}
