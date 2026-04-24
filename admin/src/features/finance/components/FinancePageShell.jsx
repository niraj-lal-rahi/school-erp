import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { AppDataTable } from '../../../components/common/AppDataTable';

export function FinancePageShell({
  title,
  description,
  form,
  setForm,
  onSubmit,
  submitLabel,
  saving,
  error,
  fields,
  columns,
  rows,
  search,
  setSearch,
  filters = [],
  onEdit,
  onDelete,
  emptyState,
}) {
  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={onSubmit}>
            <Stack spacing={0.5}>
              <Typography variant="h5">{title}</Typography>
              <Typography variant="body2" color="text.secondary">{description}</Typography>
            </Stack>

            {error ? <Alert severity="error">{error}</Alert> : null}

            {fields.map((field) => (
              <TextField
                key={field.key}
                label={field.label}
                value={form[field.key] ?? ''}
                onChange={(event) => setForm((current) => ({
                  ...current,
                  [field.key]: field.type === 'number'
                    ? event.target.value
                    : field.type === 'select-boolean'
                      ? event.target.value === 'true'
                      : event.target.value,
                }))}
                select={field.type === 'select' || field.type === 'select-boolean'}
                multiline={field.type === 'textarea'}
                minRows={field.type === 'textarea' ? 3 : undefined}
                type={field.type === 'date' ? 'date' : 'text'}
                InputLabelProps={field.type === 'date' ? { shrink: true } : undefined}
              >
                {(field.options || []).map((option) => (
                  <MenuItem key={`${field.key}-${option.value}`} value={option.value}>
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
          title={`${title} List`}
          columns={[
            ...columns,
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => onEdit(row)}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => onDelete(row.id)}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={rows}
          loading={false}
          searchValue={search}
          onSearchChange={setSearch}
          filters={filters}
          emptyState={emptyState}
        />
      </Grid>
    </Grid>
  );
}
