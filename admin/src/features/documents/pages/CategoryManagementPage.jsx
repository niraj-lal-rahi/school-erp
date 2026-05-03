import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, MenuItem, Stack, Switch, TextField, FormControlLabel } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import {
  createDocumentCategory,
  deleteDocumentCategory,
  fetchDocumentCategories,
  updateDocumentCategory,
} from '../store/documentsSlice';

const initialForm = {
  name: '',
  code: '',
  applies_to: 'general',
  requires_verification: false,
  has_expiry: false,
  status: 'active',
};

export function CategoryManagementPage() {
  const dispatch = useAppDispatch();
  const { categories, categoriesPagination, loading, saving, error } = useAppSelector((state) => state.documents);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchDocumentCategories({ per_page: 50, page: categoriesPagination.page }));
  }, [dispatch, categoriesPagination.page]);

  const rows = categories.filter((category) => (`${category.name} ${category.code}`).toLowerCase().includes(search.toLowerCase()));

  return (
    <DocumentsPageShell
      title="Category Management"
      description="Shape how documents behave by type, including verification gates and expiry tracking expectations."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} flexWrap="wrap">
        <TextField label="Category Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} fullWidth />
        <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toUpperCase().replaceAll(' ', '_') }))} fullWidth />
        <TextField select label="Applies To" value={form.applies_to} onChange={(event) => setForm((current) => ({ ...current, applies_to: event.target.value }))} sx={{ minWidth: 180 }}>
          {['student', 'staff', 'tenant', 'finance', 'academic', 'general'].map((value) => (
            <MenuItem key={value} value={value}>{value}</MenuItem>
          ))}
        </TextField>
        <FormControlLabel control={<Switch checked={form.requires_verification} onChange={(event) => setForm((current) => ({ ...current, requires_verification: event.target.checked }))} />} label="Requires Verification" />
        <FormControlLabel control={<Switch checked={form.has_expiry} onChange={(event) => setForm((current) => ({ ...current, has_expiry: event.target.checked }))} />} label="Tracks Expiry" />
        <Button
          variant="contained"
          startIcon={<AddOutlinedIcon />}
          disabled={saving || !form.name || !form.code}
          onClick={() => dispatch(createDocumentCategory(form)).then((result) => {
            if (!result.error) {
              setForm(initialForm);
            }
          })}
        >
          Create
        </Button>
      </Stack>

      <AppDataTable
        title="Categories"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...categoriesPagination,
          onPageChange: (page) => dispatch(fetchDocumentCategories({ per_page: 50, page })),
        }}
        filters={[]}
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'code', header: 'Code' },
          { key: 'applies_to', header: 'Applies To' },
          { key: 'requires_verification', header: 'Verification', render: (row) => (row.requires_verification ? 'Required' : 'Optional') },
          { key: 'has_expiry', header: 'Expiry', render: (row) => (row.has_expiry ? 'Tracked' : 'Not tracked') },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button size="small" onClick={() => dispatch(updateDocumentCategory({ id: row.id, payload: { status: row.status === 'active' ? 'inactive' : 'active' } }))}>
                  Toggle Status
                </Button>
                <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deleteDocumentCategory(row.id))}>
                  Delete
                </Button>
              </Stack>
            ),
          },
        ]}
      />
    </DocumentsPageShell>
  );
}
