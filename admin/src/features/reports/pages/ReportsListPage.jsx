import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import EditOutlinedIcon from '@mui/icons-material/EditOutlined';
import { Alert, Button, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { ReportsFormDrawer } from '../components/ReportsFormDrawer';
import { ReportsPageShell } from '../components/ReportsPageShell';
import { useReportsAccess } from '../hooks/useReportsAccess';
import { createReportDefinition, deleteReportDefinition, fetchReportDefinitions, updateReportDefinition } from '../store/reportsSlice';
import { reportModuleOptions, reportStatusOptions } from '../types/options';

const initialForm = {
  name: '',
  code: '',
  module: 'attendance',
  description: '',
  query_config: '{\n  "metrics": []\n}',
  default_filters: '{}',
  is_system: false,
  status: 'active',
};

export function ReportsListPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useReportsAccess();
  const { definitions, definitionsPagination, loading, saving, error } = useAppSelector((state) => state.reports);
  const [search, setSearch] = useState('');
  const [filters, setFilters] = useState({ module: '', status: '' });
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchReportDefinitions({
      search: search || undefined,
      module: filters.module || undefined,
      status: filters.status || undefined,
      per_page: 20,
      page: definitionsPagination.page,
    }));
  }, [dispatch, search, filters, definitionsPagination.page]);

  const formError = useMemo(() => {
    try {
      JSON.parse(form.query_config || '{}');
      JSON.parse(form.default_filters || '{}');
      return null;
    } catch {
      return 'Query config and default filters must be valid JSON.';
    }
  }, [form]);

  function openCreate() {
    setEditing(null);
    setForm(initialForm);
    setDrawerOpen(true);
  }

  function openEdit(item) {
    setEditing(item);
    setForm({
      name: item.name || '',
      code: item.code || '',
      module: item.module || 'attendance',
      description: item.description || '',
      query_config: JSON.stringify(item.query_config || {}, null, 2),
      default_filters: JSON.stringify(item.default_filters || {}, null, 2),
      is_system: Boolean(item.is_system),
      status: item.status || 'active',
    });
    setDrawerOpen(true);
  }

  async function handleSubmit(event) {
    event.preventDefault();
    if (formError) {
      return;
    }

    const payload = {
      name: form.name,
      code: form.code,
      module: form.module,
      description: form.description || null,
      query_config: JSON.parse(form.query_config || '{}'),
      default_filters: JSON.parse(form.default_filters || '{}'),
      is_system: Boolean(form.is_system),
      status: form.status,
    };

    if (editing) {
      await dispatch(updateReportDefinition({ id: editing.id, payload }));
    } else {
      await dispatch(createReportDefinition(payload));
    }

    setDrawerOpen(false);
  }

  return (
    <ReportsPageShell
      title="Reports List"
      description="Browse the analytics definitions that power dashboards, exports, scheduled digests, and ad-hoc reporting across the ERP."
      actions={canManage ? (
        <Button variant="contained" startIcon={<AddOutlinedIcon />} onClick={openCreate}>
          New Definition
        </Button>
      ) : null}
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Report Definitions"
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'code', header: 'Code' },
          { key: 'module', header: 'Module' },
          { key: 'status', header: 'Status' },
          { key: 'runs_count', header: 'Runs' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => canManage ? (
              <Stack direction="row" spacing={1}>
                <Button size="small" startIcon={<EditOutlinedIcon />} onClick={() => openEdit(row)}>Edit</Button>
                <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deleteReportDefinition(row.id))}>Delete</Button>
              </Stack>
            ) : 'View only',
          },
        ]}
        rows={definitions}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        filters={[
          {
            key: 'module',
            label: 'Module',
            value: filters.module,
            onChange: (value) => setFilters((current) => ({ ...current, module: value })),
            options: reportModuleOptions,
          },
          {
            key: 'status',
            label: 'Status',
            value: filters.status,
            onChange: (value) => setFilters((current) => ({ ...current, status: value })),
            options: reportStatusOptions,
          },
        ]}
        pagination={{
          ...definitionsPagination,
          onPageChange: (page) => dispatch(fetchReportDefinitions({
            search: search || undefined,
            module: filters.module || undefined,
            status: filters.status || undefined,
            per_page: 20,
            page,
          })),
        }}
        emptyState="No report definitions available."
      />

      <ReportsFormDrawer
        open={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        title={editing ? 'Edit Report Definition' : 'Create Report Definition'}
        subtitle="Keep the configuration JSON valid so the backend can interpret the report shape safely."
      >
        <Stack component="form" spacing={2} onSubmit={handleSubmit}>
          <TextField fullWidth label="Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} required />
          <TextField fullWidth label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toUpperCase().replace(/\s+/g, '-') }))} required />
          <TextField select fullWidth label="Module" value={form.module} onChange={(event) => setForm((current) => ({ ...current, module: event.target.value }))}>
            {reportModuleOptions.filter((option) => option.value).map((option) => (
              <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
            ))}
          </TextField>
          <TextField fullWidth multiline minRows={2} label="Description" value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />
          <TextField fullWidth multiline minRows={8} label="Query Config (JSON)" value={form.query_config} onChange={(event) => setForm((current) => ({ ...current, query_config: event.target.value }))} />
          <TextField fullWidth multiline minRows={5} label="Default Filters (JSON)" value={form.default_filters} onChange={(event) => setForm((current) => ({ ...current, default_filters: event.target.value }))} />
          <TextField select fullWidth label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
            {reportStatusOptions.filter((option) => option.value).map((option) => (
              <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
            ))}
          </TextField>
          {formError ? <Alert severity="warning">{formError}</Alert> : null}
          <Stack direction="row" justifyContent="flex-end" spacing={1.5}>
            <Button variant="text" onClick={() => setDrawerOpen(false)}>Cancel</Button>
            <Button type="submit" variant="contained" disabled={saving || Boolean(formError)}>
              {saving ? 'Saving...' : editing ? 'Update Definition' : 'Create Definition'}
            </Button>
          </Stack>
        </Stack>
      </ReportsFormDrawer>
    </ReportsPageShell>
  );
}
