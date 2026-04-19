import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import {
  Alert,
  Button,
  Grid,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useState } from 'react';

const initialValues = {
  blood_group: '',
  height: '',
  weight: '',
  allergies: '',
  medical_conditions: '',
  medications: '',
  doctor_name: '',
  doctor_phone: '',
  hospital_name: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  insurance_provider: '',
  insurance_number: '',
  notes: '',
};

export function StudentMedicalPanel({ record, onSave, saving, error, readOnly = false }) {
  const [values, setValues] = useState(initialValues);

  useEffect(() => {
    setValues({
      ...initialValues,
      ...(record || {}),
    });
  }, [record]);

  function handleChange(event) {
    const { name, value } = event.target;
    setValues((current) => ({
      ...current,
      [name]: value,
    }));
  }

  function handleSubmit(event) {
    event.preventDefault();
    onSave(values);
  }

  return (
    <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2.5}>
        <Stack spacing={0.5}>
          <Typography variant="h6">Medical Information</Typography>
          <Typography variant="body2" color="text.secondary">
            Capture emergency and health information that staff may need during school activities.
          </Typography>
        </Stack>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Blood Group" name="blood_group" value={values.blood_group} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Height (cm)" name="height" value={values.height} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Weight (kg)" name="weight" value={values.weight} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Doctor Name" name="doctor_name" value={values.doctor_name} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Doctor Phone" name="doctor_phone" value={values.doctor_phone} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Hospital Name" name="hospital_name" value={values.hospital_name} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Emergency Contact Name" name="emergency_contact_name" value={values.emergency_contact_name} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Emergency Contact Phone" name="emergency_contact_phone" value={values.emergency_contact_phone} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Insurance Provider" name="insurance_provider" value={values.insurance_provider} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Insurance Number" name="insurance_number" value={values.insurance_number} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth multiline minRows={3} label="Allergies" name="allergies" value={values.allergies} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth multiline minRows={3} label="Medical Conditions" name="medical_conditions" value={values.medical_conditions} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth multiline minRows={3} label="Medications" name="medications" value={values.medications} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
          <Grid size={{ xs: 12 }}>
            <TextField fullWidth multiline minRows={4} label="Notes" name="notes" value={values.notes} onChange={handleChange} disabled={readOnly || saving} />
          </Grid>
        </Grid>

        {readOnly ? null : (
          <Stack direction="row" justifyContent="flex-end">
            <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={saving}>
              {saving ? 'Saving...' : 'Save Medical Record'}
            </Button>
          </Stack>
        )}
      </Stack>
    </Paper>
  );
}
