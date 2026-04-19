import PlayCircleOutlineOutlinedIcon from '@mui/icons-material/PlayCircleOutlineOutlined';
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

const actionFields = {
  promote: ['academic_year_id', 'school_class_id', 'section_id', 'roll_number'],
  transfer_section: ['section_id', 'roll_number'],
  withdraw: [],
  graduate: [],
  suspend: [],
  reactivate: [],
};

const actionOptions = [
  { value: 'promote', label: 'Promote Student' },
  { value: 'transfer_section', label: 'Transfer Section' },
  { value: 'withdraw', label: 'Withdraw Student' },
  { value: 'graduate', label: 'Graduate Student' },
  { value: 'suspend', label: 'Suspend Student' },
  { value: 'reactivate', label: 'Reactivate Student' },
];

export function StudentLifecyclePanel({ student, onSubmit, saving, error }) {
  const { academicYears, classes } = useAppSelector((state) => state.masterData);
  const [values, setValues] = useState({
    action: 'suspend',
    academic_year_id: '',
    school_class_id: '',
    section_id: '',
    roll_number: '',
    effective_date: '',
    reason: '',
  });

  const availableSections = useMemo(() => {
    const selectedClass = classes.find((item) => Number(item.id) === Number(values.school_class_id));
    return selectedClass?.sections || [];
  }, [classes, values.school_class_id]);

  const requiredFields = actionFields[values.action] || [];

  function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      effective_date: values.effective_date,
      reason: values.reason,
    };

    requiredFields.forEach((field) => {
      if (values[field]) {
        payload[field] = values[field];
      }
    });

    onSubmit(values.action, payload);
  }

  return (
    <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2.5}>
        <Stack spacing={0.5}>
          <Typography variant="h6">Lifecycle Actions</Typography>
          <Typography variant="body2" color="text.secondary">
            Manage promotion, section transfer, withdrawal, graduation, suspension, and reactivation for {student.full_name}.
          </Typography>
        </Stack>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <Grid container spacing={2}>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField select fullWidth label="Action" value={values.action} onChange={(event) => setValues((current) => ({ ...current, action: event.target.value }))}>
              {actionOptions.map((option) => (
                <MenuItem key={option.value} value={option.value}>
                  {option.label}
                </MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField
              fullWidth
              type="date"
              label="Effective Date"
              value={values.effective_date}
              onChange={(event) => setValues((current) => ({ ...current, effective_date: event.target.value }))}
              InputLabelProps={{ shrink: true }}
            />
          </Grid>

          {requiredFields.includes('academic_year_id') ? (
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField select fullWidth label="Academic Year" value={values.academic_year_id} onChange={(event) => setValues((current) => ({ ...current, academic_year_id: event.target.value }))}>
                {academicYears.map((year) => (
                  <MenuItem key={year.id} value={year.id}>
                    {year.name}
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
          ) : null}

          {requiredFields.includes('school_class_id') ? (
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField select fullWidth label="Class" value={values.school_class_id} onChange={(event) => setValues((current) => ({ ...current, school_class_id: event.target.value }))}>
                {classes.map((schoolClass) => (
                  <MenuItem key={schoolClass.id} value={schoolClass.id}>
                    {schoolClass.name} ({schoolClass.code})
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
          ) : null}

          {requiredFields.includes('section_id') ? (
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField select fullWidth label="Section" value={values.section_id} onChange={(event) => setValues((current) => ({ ...current, section_id: event.target.value }))}>
                {availableSections.map((section) => (
                  <MenuItem key={section.id} value={section.id}>
                    {section.name}
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
          ) : null}

          {requiredFields.includes('roll_number') ? (
            <Grid size={{ xs: 12, md: 4 }}>
              <TextField fullWidth label="Roll Number" value={values.roll_number} onChange={(event) => setValues((current) => ({ ...current, roll_number: event.target.value }))} />
            </Grid>
          ) : null}

          <Grid size={{ xs: 12 }}>
            <TextField fullWidth multiline minRows={3} label="Reason / Remarks" value={values.reason} onChange={(event) => setValues((current) => ({ ...current, reason: event.target.value }))} />
          </Grid>
        </Grid>

        <Stack direction="row" justifyContent="flex-end">
          <Button type="submit" variant="contained" startIcon={<PlayCircleOutlineOutlinedIcon />} disabled={saving}>
            {saving ? 'Processing...' : 'Run Action'}
          </Button>
        </Stack>
      </Stack>
    </Paper>
  );
}
