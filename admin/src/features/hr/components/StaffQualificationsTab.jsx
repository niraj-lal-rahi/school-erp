import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, Paper, Stack, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';

const initialForm = {
  id: null,
  degree: '',
  institution: '',
  board_or_university: '',
  specialization: '',
  passing_year: '',
  percentage_or_grade: '',
  document_path: '',
};

export function StaffQualificationsTab({ items, saving, error, onCreate, onUpdate, onDelete }) {
  const [form, setForm] = useState(initialForm);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      passing_year: form.passing_year ? Number(form.passing_year) : null,
      document_path: form.document_path || null,
    };
    const result = form.id ? await onUpdate(form.id, payload) : await onCreate(payload);

    if (!result?.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Qualifications"
          columns={[
            { key: 'degree', header: 'Degree' },
            { key: 'institution', header: 'Institution' },
            { key: 'specialization', header: 'Specialization' },
            { key: 'passing_year', header: 'Passing Year' },
            { key: 'percentage_or_grade', header: 'Grade' },
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
          emptyState="No qualifications added yet."
        />
      </Grid>

      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h6">{form.id ? 'Edit Qualification' : 'Add Qualification'}</Typography>
            {error ? <Alert severity="error">{error}</Alert> : null}
            <TextField label="Degree" value={form.degree} onChange={(event) => setForm((current) => ({ ...current, degree: event.target.value }))} />
            <TextField label="Institution" value={form.institution} onChange={(event) => setForm((current) => ({ ...current, institution: event.target.value }))} />
            <TextField label="Board / University" value={form.board_or_university} onChange={(event) => setForm((current) => ({ ...current, board_or_university: event.target.value }))} />
            <TextField label="Specialization" value={form.specialization} onChange={(event) => setForm((current) => ({ ...current, specialization: event.target.value }))} />
            <TextField label="Passing Year" value={form.passing_year} onChange={(event) => setForm((current) => ({ ...current, passing_year: event.target.value }))} />
            <TextField label="Grade / Percentage" value={form.percentage_or_grade} onChange={(event) => setForm((current) => ({ ...current, percentage_or_grade: event.target.value }))} />
            <TextField label="Document Path" value={form.document_path} onChange={(event) => setForm((current) => ({ ...current, document_path: event.target.value }))} />
            <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : form.id ? 'Update' : 'Save'}</Button>
          </Stack>
        </Paper>
      </Grid>
    </Grid>
  );
}
