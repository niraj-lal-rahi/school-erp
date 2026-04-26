import {
  Box,
  Button,
  Divider,
  Drawer,
  FormControlLabel,
  MenuItem,
  Stack,
  Switch,
  TextField,
  Typography,
} from '@mui/material';

function FieldRenderer({ field, form, setForm }) {
  if (field.type === 'switch') {
    return (
      <FormControlLabel
        control={(
          <Switch
            checked={Boolean(form[field.key])}
            onChange={(event) => setForm((current) => ({ ...current, [field.key]: event.target.checked }))}
          />
        )}
        label={field.label}
      />
    );
  }

  return (
    <TextField
      fullWidth
      select={field.type === 'select'}
      multiline={field.type === 'multiline'}
      minRows={field.type === 'multiline' ? (field.rows || 4) : undefined}
      type={field.type === 'datetime' ? 'datetime-local' : field.type === 'date' ? 'date' : field.type === 'number' ? 'number' : 'text'}
      label={field.label}
      value={form[field.key] ?? ''}
      onChange={(event) => setForm((current) => ({ ...current, [field.key]: event.target.value }))}
      InputLabelProps={field.type === 'date' || field.type === 'datetime' ? { shrink: true } : undefined}
      helperText={field.helperText}
    >
      {(field.options || []).map((option) => (
        <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
      ))}
    </TextField>
  );
}

export function CommunicationFormDrawer({
  open,
  onClose,
  title,
  description,
  form,
  setForm,
  fields,
  onSubmit,
  submitLabel,
  saving,
  extraContent,
}) {
  return (
    <Drawer anchor="right" open={open} onClose={onClose}>
      <Box sx={{ width: { xs: '100vw', sm: 520 }, p: 3 }}>
        <Stack spacing={2.5}>
          <Stack spacing={0.75}>
            <Typography variant="h6">{title}</Typography>
            <Typography variant="body2" color="text.secondary">
              {description}
            </Typography>
          </Stack>

          <Divider />

          <Stack component="form" spacing={2} onSubmit={onSubmit}>
            {fields.map((field) => (
              <FieldRenderer
                key={field.key}
                field={field}
                form={form}
                setForm={setForm}
              />
            ))}

            {extraContent}

            <Stack direction="row" spacing={1.5} justifyContent="flex-end">
              <Button variant="text" onClick={onClose}>Close</Button>
              <Button type="submit" variant="contained" disabled={saving}>
                {submitLabel}
              </Button>
            </Stack>
          </Stack>
        </Stack>
      </Box>
    </Drawer>
  );
}
