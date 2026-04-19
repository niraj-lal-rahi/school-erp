import { Button, Checkbox, FormControlLabel, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';

function resolveOptions(field, options) {
  if (field.options) {
    return field.options;
  }

  if (!field.optionsKey) {
    return [];
  }

  const source = options[field.optionsKey] || [];
  return source.map((item) => field.mapOption ? field.mapOption(item) : ({ value: item.id, label: item.name || item.title || item.code }));
}

export function AcademicManagementForm({ title, description, fields, values, onChange, onSubmit, saving, error, options }) {
  return (
    <Paper component="form" onSubmit={onSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2.5}>
        <div>
          <Typography variant="h5">{title}</Typography>
          <Typography variant="body2" color="text.secondary">{description}</Typography>
          {error ? <Typography color="error" mt={1}>{error}</Typography> : null}
        </div>

        <Grid container spacing={2}>
          {fields.map((field) => (
            <Grid key={field.key} size={{ xs: 12, md: field.md || 6 }}>
              {field.type === 'checkbox' ? (
                <FormControlLabel
                  control={<Checkbox checked={Boolean(values[field.key])} onChange={(event) => onChange(field.key, event.target.checked)} />}
                  label={field.label}
                />
              ) : (
                <TextField
                  fullWidth
                  select={field.type === 'select'}
                  multiline={field.type === 'textarea'}
                  minRows={field.type === 'textarea' ? 3 : undefined}
                  type={field.type === 'date' || field.type === 'datetime-local' || field.type === 'number' ? field.type : 'text'}
                  label={field.label}
                  value={values[field.key] ?? ''}
                  onChange={(event) => onChange(field.key, field.type === 'number' ? event.target.value : event.target.value)}
                  InputLabelProps={field.type === 'date' || field.type === 'datetime-local' ? { shrink: true } : undefined}
                >
                  {field.type === 'select' && resolveOptions(field, options).map((option) => (
                    <MenuItem key={option.value} value={option.value}>
                      {option.label}
                    </MenuItem>
                  ))}
                </TextField>
              )}
            </Grid>
          ))}
        </Grid>

        <Button type="submit" variant="contained" disabled={saving}>
          {saving ? 'Saving...' : 'Save'}
        </Button>
      </Stack>
    </Paper>
  );
}
