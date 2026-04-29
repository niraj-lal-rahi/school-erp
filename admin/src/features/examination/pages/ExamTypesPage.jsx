import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ExaminationPageShell } from '../components/ExaminationPageShell';
import { createExamType, deleteExamType, fetchExamTypes, updateExamType } from '../store/examinationSlice';

const initialForm = {
  id: null,
  name: '',
  code: '',
  description: '',
  status: 'active',
};

export function ExamTypesPage() {
  const dispatch = useAppDispatch();
  const { examTypes, loading, saving, error } = useAppSelector((state) => state.examination);
  const canManage = useAppSelector((state) => state.auth.user?.permissions?.includes('exams.manage'));
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    dispatch(fetchExamTypes());
  }, [dispatch]);

  const rows = useMemo(() => {
    const query = search.trim().toLowerCase();
    return examTypes.filter((item) => {
      const matchesSearch = !query || `${item.name} ${item.code} ${item.description || ''}`.toLowerCase().includes(query);
      const matchesStatus = !statusFilter || item.status === statusFilter;
      return matchesSearch && matchesStatus;
    });
  }, [examTypes, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    if (!canManage) return;

    const action = form.id
      ? updateExamType({ id: form.id, payload: { ...form, id: undefined } })
      : createExamType(form);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <ExaminationPageShell
      title="Exam Types"
      description="Manage the master list of unit tests, terms, finals, and other exam categories before building actual exams."
    >
      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack component="form" spacing={2} onSubmit={handleSubmit}>
              <Typography variant="h6">{form.id ? 'Edit Exam Type' : 'Create Exam Type'}</Typography>
              {error ? <Alert severity="error">{error}</Alert> : null}
              <TextField label="Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} />
              <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} />
              <TextField label="Description" multiline minRows={3} value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />
              <TextField select label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                <MenuItem value="active">Active</MenuItem>
                <MenuItem value="inactive">Inactive</MenuItem>
              </TextField>
              <Button type="submit" variant="contained" disabled={saving || !canManage}>
                {saving ? 'Saving...' : form.id ? 'Update Exam Type' : 'Create Exam Type'}
              </Button>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, lg: 8 }}>
          <AppDataTable
            title="Exam Types List"
            columns={[
              { key: 'name', header: 'Name' },
              { key: 'code', header: 'Code' },
              { key: 'description', header: 'Description' },
              { key: 'status', header: 'Status' },
              {
                key: 'actions',
                header: 'Actions',
                render: (row) => canManage ? (
                  <Stack direction="row" spacing={1}>
                    <IconButton color="primary" onClick={() => setForm({
                      id: row.id,
                      name: row.name || '',
                      code: row.code || '',
                      description: row.description || '',
                      status: row.status || 'active',
                    })}>
                      <EditOutlinedIcon />
                    </IconButton>
                    <IconButton color="error" onClick={() => dispatch(deleteExamType(row.id))}>
                      <DeleteOutlineOutlinedIcon />
                    </IconButton>
                  </Stack>
                ) : 'View only',
              },
            ]}
            rows={rows}
            loading={loading}
            searchValue={search}
            onSearchChange={setSearch}
            filters={[
              {
                key: 'status',
                label: 'Status',
                value: statusFilter,
                onChange: setStatusFilter,
                options: [
                  { value: '', label: 'All' },
                  { value: 'active', label: 'Active' },
                  { value: 'inactive', label: 'Inactive' },
                ],
              },
            ]}
            emptyState="No exam types created yet."
          />
        </Grid>
      </Grid>
    </ExaminationPageShell>
  );
}
