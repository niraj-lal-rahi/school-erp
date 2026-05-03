import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import LabelOutlinedIcon from '@mui/icons-material/LabelOutlined';
import { Alert, Button, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import { createDocumentTag, deleteDocumentTag, fetchDocumentTags } from '../store/documentsSlice';

export function TagsManagementPage() {
  const dispatch = useAppDispatch();
  const { tags, tagsPagination, loading, saving, error } = useAppSelector((state) => state.documents);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState({ name: '', code: '' });

  useEffect(() => {
    dispatch(fetchDocumentTags({ per_page: 50, page: tagsPagination.page }));
  }, [dispatch, tagsPagination.page]);

  const rows = tags.filter((tag) => (`${tag.name} ${tag.code}`).toLowerCase().includes(search.toLowerCase()));

  return (
    <DocumentsPageShell
      title="Tags Management"
      description="Keep search and categorization sharp with lightweight tags that can travel across document types."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
        <TextField label="Tag Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} fullWidth />
        <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toUpperCase().replaceAll(' ', '_') }))} fullWidth />
        <Button
          variant="contained"
          startIcon={<LabelOutlinedIcon />}
          disabled={saving || !form.name || !form.code}
          onClick={() => dispatch(createDocumentTag(form)).then((result) => {
            if (!result.error) {
              setForm({ name: '', code: '' });
            }
          })}
        >
          Create
        </Button>
      </Stack>

      <AppDataTable
        title="Tags"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...tagsPagination,
          onPageChange: (page) => dispatch(fetchDocumentTags({ per_page: 50, page })),
        }}
        filters={[]}
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'code', header: 'Code' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deleteDocumentTag(row.id))}>
                Delete
              </Button>
            ),
          },
        ]}
      />
    </DocumentsPageShell>
  );
}
