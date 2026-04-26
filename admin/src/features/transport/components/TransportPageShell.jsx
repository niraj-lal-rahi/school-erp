import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { PermissionGate } from '../../../components/common/PermissionGate';

export function TransportPageShell({
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
  pagination,
  onEdit,
  onDelete,
  emptyState,
}) {
  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <PermissionGate permission="transport.manage">
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
                      : event.target.value,
                  }))}
                  select={field.type === 'select'}
                  multiline={field.type === 'textarea'}
                  minRows={field.type === 'textarea' ? 3 : undefined}
                  type={field.type === 'date' ? 'date' : field.type === 'datetime-local' ? 'datetime-local' : 'text'}
                  InputLabelProps={field.type === 'date' || field.type === 'datetime-local' ? { shrink: true } : undefined}
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
        </PermissionGate>
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
                <PermissionGate permission="transport.manage" fallback={null}>
                  <Stack direction="row" spacing={1}>
                    <IconButton color="primary" onClick={() => onEdit(row)}>
                      <EditOutlinedIcon />
                    </IconButton>
                    <IconButton color="error" onClick={() => onDelete(row.id)}>
                      <DeleteOutlineOutlinedIcon />
                    </IconButton>
                  </Stack>
                </PermissionGate>
              ),
            },
          ]}
          rows={rows}
          loading={false}
          searchValue={search}
          onSearchChange={setSearch}
          filters={filters}
          pagination={pagination}
          emptyState={emptyState}
        />
      </Grid>
    </Grid>
  );
}
