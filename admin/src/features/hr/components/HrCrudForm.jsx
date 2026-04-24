import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';

export function HrCrudForm({
  title,
  description,
  fields,
  values,
  onChange,
  onSubmit,
  submitLabel,
  saving,
  error,
  validationErrors = {},
}) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack component="form" spacing={2} onSubmit={onSubmit}>
        <Stack spacing={0.5}>
          <Typography variant="h5">{title}</Typography>
          <Typography variant="body2" color="text.secondary">
            {description}
          </Typography>
        </Stack>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <Grid container spacing={2}>
          {fields.map((field) => (
            <Grid key={field.key} size={{ xs: 12, md: field.grid || 6 }}>
              <TextField
                fullWidth
                select={field.type === 'select'}
                multiline={field.type === 'textarea'}
                minRows={field.type === 'textarea' ? 3 : undefined}
                type={field.type === 'number' ? 'number' : field.type === 'date' ? 'date' : 'text'}
                label={field.label}
                value={values[field.key] ?? (field.type === 'checkbox' ? false : '')}
                onChange={(event) => {
                  const nextValue = field.type === 'checkbox'
                    ? event.target.checked
                    : field.type === 'number'
                      ? event.target.value === '' ? '' : Number(event.target.value)
                      : event.target.value;
                  onChange(field.key, nextValue);
                }}
                helperText={validationErrors[field.key] || field.helperText}
                error={Boolean(validationErrors[field.key])}
                InputLabelProps={field.type === 'date' ? { shrink: true } : undefined}
              >
                {(field.options || []).map((option) => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </TextField>
            </Grid>
          ))}
        </Grid>

        <Button type="submit" variant="contained" disabled={saving}>
          {saving ? 'Saving...' : submitLabel}
        </Button>
      </Stack>
    </Paper>
  );
}
