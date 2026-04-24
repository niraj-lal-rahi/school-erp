import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, Paper, Stack, Switch, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';

const initialForm = {
  id: null,
  organization_name: '',
  designation: '',
  start_date: '',
  end_date: '',
  is_current: false,
  responsibilities: '',
  experience_letter_path: '',
};

export function StaffExperienceTab({ items, saving, error, onCreate, onUpdate, onDelete }) {
  const [form, setForm] = useState(initialForm);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = form.id ? await onUpdate(form.id, form) : await onCreate(form);

    if (!result?.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Work Experience"
          columns={[
            { key: 'organization_name', header: 'Organization' },
            { key: 'designation', header: 'Designation' },
            { key: 'duration', header: 'Duration', render: (row) => `${row.start_date} to ${row.end_date || 'Present'}` },
            { key: 'is_current', header: 'Current', render: (row) => row.is_current ? 'Yes' : 'No' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm(row)}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => onDelete(row.id)}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={items}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No prior work experience saved."
        />
      </Grid>

      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h6">{form.id ? 'Edit Experience' : 'Add Experience'}</Typography>
            {error ? <Alert severity="error">{error}</Alert> : null}
            <TextField label="Organization" value={form.organization_name} onChange={(event) => setForm((current) => ({ ...current, organization_name: event.target.value }))} />
            <TextField label="Designation" value={form.designation} onChange={(event) => setForm((current) => ({ ...current, designation: event.target.value }))} />
            <TextField type="date" label="Start Date" value={form.start_date} onChange={(event) => setForm((current) => ({ ...current, start_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
            <TextField type="date" label="End Date" value={form.end_date} onChange={(event) => setForm((current) => ({ ...current, end_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
            <Stack direction="row" justifyContent="space-between" alignItems="center">
              <Typography variant="body2">Current Role</Typography>
              <Switch checked={Boolean(form.is_current)} onChange={(event) => setForm((current) => ({ ...current, is_current: event.target.checked }))} />
            </Stack>
            <TextField multiline minRows={3} label="Responsibilities" value={form.responsibilities} onChange={(event) => setForm((current) => ({ ...current, responsibilities: event.target.value }))} />
            <TextField label="Experience Letter Path" value={form.experience_letter_path} onChange={(event) => setForm((current) => ({ ...current, experience_letter_path: event.target.value }))} />
            <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : form.id ? 'Update' : 'Save'}</Button>
          </Stack>
        </Paper>
      </Grid>
    </Grid>
  );
}
