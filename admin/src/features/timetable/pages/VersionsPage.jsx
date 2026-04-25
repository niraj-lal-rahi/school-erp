import ArchiveOutlinedIcon from '@mui/icons-material/ArchiveOutlined';
import ContentCopyOutlinedIcon from '@mui/icons-material/ContentCopyOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import PublishOutlinedIcon from '@mui/icons-material/PublishOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import {
  archiveTimetableVersion,
  createTimetableVersion,
  deleteTimetableVersion,
  duplicateTimetableVersion,
  fetchTimetableOptions,
  fetchTimetableVersions,
  publishTimetableVersion,
  updateTimetableVersion,
} from '../store/timetableSlice';

const initialForm = {
  id: null,
  academic_year_id: '',
  name: '',
  code: '',
  effective_from: '',
  effective_to: '',
  status: 'draft',
};

export function VersionsPage() {
  const dispatch = useAppDispatch();
  const { versions, options, loading, saving, error } = useAppSelector((state) => state.timetable);
  const [form, setForm] = useState(initialForm);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => {
    dispatch(fetchTimetableVersions());
    dispatch(fetchTimetableOptions());
  }, [dispatch]);

  const filteredRows = useMemo(() => {
    const query = search.trim().toLowerCase();

    return versions.filter((item) => {
      const matchesSearch = !query || `${item.name} ${item.code} ${item.academic_year?.name || ''}`.toLowerCase().includes(query);
      const matchesStatus = !statusFilter || item.status === statusFilter;

      return matchesSearch && matchesStatus;
    });
  }, [versions, search, statusFilter]);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = {
      ...form,
      academic_year_id: Number(form.academic_year_id),
      effective_to: form.effective_to || null,
    };

    const action = form.id
      ? updateTimetableVersion({ id: form.id, payload: { ...payload, id: undefined } })
      : createTimetableVersion(payload);

    const result = await dispatch(action);
    if (!result.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Stack spacing={0.5}>
              <Typography variant="h5">Timetable Versions</Typography>
              <Typography variant="body2" color="text.secondary">
                Manage draft, published, and archived timetable releases for each academic year.
              </Typography>
            </Stack>

            {error ? <Alert severity="error">{error}</Alert> : null}

            <TextField
              select
              label="Academic Year"
              value={form.academic_year_id}
              onChange={(event) => setForm((current) => ({ ...current, academic_year_id: event.target.value }))}
            >
              {(options.academicYears || []).map((year) => (
                <MenuItem key={year.id} value={year.id}>
                  {year.name}
                </MenuItem>
              ))}
            </TextField>

            <TextField label="Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} />
            <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} />
            <TextField
              label="Effective From"
              type="date"
              value={form.effective_from}
              onChange={(event) => setForm((current) => ({ ...current, effective_from: event.target.value }))}
              InputLabelProps={{ shrink: true }}
            />
            <TextField
              label="Effective To"
              type="date"
              value={form.effective_to}
              onChange={(event) => setForm((current) => ({ ...current, effective_to: event.target.value }))}
              InputLabelProps={{ shrink: true }}
            />
            <TextField
              select
              label="Status"
              value={form.status}
              onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
            >
              <MenuItem value="draft">Draft</MenuItem>
              <MenuItem value="published">Published</MenuItem>
              <MenuItem value="archived">Archived</MenuItem>
            </TextField>

            <Button type="submit" variant="contained" disabled={saving}>
              {saving ? 'Saving...' : form.id ? 'Update Version' : 'Create Version'}
            </Button>

            {form.status === 'published' ? (
              <Alert severity="info">
                Published versions become operational and should be treated as read-only after release.
              </Alert>
            ) : null}
          </Stack>
        </Paper>
      </Grid>

      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Timetable Version List"
          columns={[
            { key: 'name', header: 'Name' },
            { key: 'code', header: 'Code' },
            { key: 'academic_year', header: 'Academic Year', render: (row) => row.academic_year?.name || '-' },
            { key: 'effective_range', header: 'Effective Range', render: (row) => `${row.effective_from}${row.effective_to ? ` to ${row.effective_to}` : ''}` },
            { key: 'status', header: 'Status' },
            { key: 'published_at', header: 'Published', render: (row) => row.published_at ? new Date(row.published_at).toLocaleString() : '-' },
            { key: 'latest_log', header: 'Latest Activity', render: (row) => row.publish_logs?.[0]?.action || '-' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <Stack direction="row" spacing={1}>
                  <IconButton color="primary" onClick={() => setForm({
                    id: row.id,
                    academic_year_id: row.academic_year_id || '',
                    name: row.name || '',
                    code: row.code || '',
                    effective_from: row.effective_from || '',
                    effective_to: row.effective_to || '',
                    status: row.status || 'draft',
                  })}>
                    <EditOutlinedIcon />
                  </IconButton>
                  <IconButton color="success" onClick={() => dispatch(publishTimetableVersion({ id: row.id }))} disabled={row.status === 'published'}>
                    <PublishOutlinedIcon />
                  </IconButton>
                  <IconButton color="warning" onClick={() => dispatch(archiveTimetableVersion({ id: row.id }))} disabled={row.status === 'archived'}>
                    <ArchiveOutlinedIcon />
                  </IconButton>
                  <IconButton color="secondary" onClick={() => dispatch(duplicateTimetableVersion(row.id))}>
                    <ContentCopyOutlinedIcon />
                  </IconButton>
                  <IconButton color="error" onClick={() => dispatch(deleteTimetableVersion(row.id))}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </Stack>
              ),
            },
          ]}
          rows={filteredRows}
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
                { value: '', label: 'All Statuses' },
                { value: 'draft', label: 'Draft' },
                { value: 'published', label: 'Published' },
                { value: 'archived', label: 'Archived' },
              ],
            },
          ]}
          emptyState="No timetable versions have been created yet."
        />
      </Grid>
    </Grid>
  );
}
