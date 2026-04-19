import {
  Alert,
  Box,
  Button,
  Grid,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { AppDataTable } from '../../../components/common/AppDataTable';

export function MasterDataPageShell({
  title,
  description,
  fields,
  formState,
  onFieldChange,
  onSubmit,
  submitLabel,
  saving,
  error,
  columns,
  rows,
}) {
  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={onSubmit}>
            <Box>
              <Typography variant="h5">{title}</Typography>
              <Typography variant="body2" color="text.secondary">
                {description}
              </Typography>
            </Box>

            {error ? <Alert severity="error">{error}</Alert> : null}

            {fields.map((field) => (
              <TextField
                key={field.key}
                type={field.type || 'text'}
                label={field.label}
                value={formState[field.key] ?? ''}
                onChange={(event) => onFieldChange(field.key, field.type === 'checkbox' ? event.target.checked : event.target.value)}
                select={field.select}
                InputLabelProps={field.type === 'date' ? { shrink: true } : undefined}
              >
                {field.options?.map((option) => (
                  <MenuItem key={option.value} value={option.value}>
                    {option.label}
                  </MenuItem>
                ))}
              </TextField>
            ))}

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : submitLabel}
            </Button>
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title={title}
          columns={columns}
          rows={rows}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState={`No ${title.toLowerCase()} found.`}
        />
      </Grid>
    </Grid>
  );
}
