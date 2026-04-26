import { Grid, MenuItem, TextField } from '@mui/material';
import { audienceOptions, recipientTypeOptions } from '../types/options';

export function AudienceSelectorFields({
  form,
  setForm,
  classes = [],
  sections = [],
  students = [],
  guardians = [],
  staffMembers = [],
}) {
  const individualType = form.individual_recipient_type || 'student';
  const individualOptions = {
    student: students,
    guardian: guardians,
    staff: staffMembers,
  }[individualType] || [];

  return (
    <Grid container spacing={2}>
      <Grid size={{ xs: 12, md: 4 }}>
        <TextField
          select
          fullWidth
          label="Audience"
          value={form.audience_type}
          onChange={(event) => setForm((current) => ({
            ...current,
            audience_type: event.target.value,
            class_id: '',
            section_id: '',
            recipient_type: '',
            recipient_id: '',
          }))}
        >
          {audienceOptions.map((option) => (
            <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
          ))}
        </TextField>
      </Grid>

      {(form.audience_type === 'class' || form.audience_type === 'section') ? (
        <Grid size={{ xs: 12, md: 4 }}>
          <TextField
            select
            fullWidth
            label="Class"
            value={form.class_id}
            onChange={(event) => setForm((current) => ({ ...current, class_id: event.target.value, section_id: '' }))}
          >
            <MenuItem value="">Select class</MenuItem>
            {classes.map((item) => (
              <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
            ))}
          </TextField>
        </Grid>
      ) : null}

      {form.audience_type === 'section' ? (
        <Grid size={{ xs: 12, md: 4 }}>
          <TextField
            select
            fullWidth
            label="Section"
            value={form.section_id}
            onChange={(event) => setForm((current) => ({ ...current, section_id: event.target.value }))}
          >
            <MenuItem value="">Select section</MenuItem>
            {sections
              .filter((section) => !form.class_id || String(section.class_id) === String(form.class_id))
              .map((item) => (
                <MenuItem key={item.id} value={item.id}>{item.name}</MenuItem>
              ))}
          </TextField>
        </Grid>
      ) : null}

      {form.audience_type === 'individual' ? (
        <>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField
              select
              fullWidth
              label="Recipient Type"
              value={individualType}
              onChange={(event) => setForm((current) => ({
                ...current,
                individual_recipient_type: event.target.value,
                recipient_type: event.target.value,
                recipient_id: '',
              }))}
            >
              {recipientTypeOptions
                .filter((option) => option.value !== 'group')
                .map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 8 }}>
            <TextField
              select
              fullWidth
              label="Recipient"
              value={form.recipient_id}
              onChange={(event) => setForm((current) => ({
                ...current,
                recipient_type: individualType,
                recipient_id: event.target.value,
              }))}
            >
              <MenuItem value="">Select recipient</MenuItem>
              {individualOptions.map((item) => (
                <MenuItem key={item.id} value={item.id}>
                  {item.full_name || item.name || item.first_name || `#${item.id}`}
                </MenuItem>
              ))}
            </TextField>
          </Grid>
        </>
      ) : null}
    </Grid>
  );
}
