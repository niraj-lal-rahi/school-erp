import CreateNewFolderOutlinedIcon from '@mui/icons-material/CreateNewFolderOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, MenuItem, Stack, TextField } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import {
  createDocumentFolder,
  deleteDocumentFolder,
  fetchDocumentFolders,
  updateDocumentFolder,
} from '../store/documentsSlice';

const initialForm = {
  name: '',
  code: '',
  description: '',
  parent_id: '',
  visibility: 'private',
};

export function FolderManagementPage() {
  const dispatch = useAppDispatch();
  const { folders, foldersPagination, loading, saving, error } = useAppSelector((state) => state.documents);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchDocumentFolders({ per_page: 50, page: foldersPagination.page }));
  }, [dispatch, foldersPagination.page]);

  const rows = folders.filter((folder) => (`${folder.name} ${folder.code || ''}`).toLowerCase().includes(search.toLowerCase()));

  return (
    <DocumentsPageShell
      title="Folder Management"
      description="Build private, internal, or shared folder structures without losing control of nesting or access boundaries."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
        <TextField label="Folder Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} fullWidth />
        <TextField label="Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value }))} fullWidth />
        <TextField select label="Parent Folder" value={form.parent_id} onChange={(event) => setForm((current) => ({ ...current, parent_id: event.target.value }))} sx={{ minWidth: 180 }}>
          <MenuItem value="">Root</MenuItem>
          {folders.map((folder) => (
            <MenuItem key={folder.id} value={folder.id}>{folder.name}</MenuItem>
          ))}
        </TextField>
        <TextField select label="Visibility" value={form.visibility} onChange={(event) => setForm((current) => ({ ...current, visibility: event.target.value }))} sx={{ minWidth: 180 }}>
          {['private', 'internal', 'shared'].map((value) => (
            <MenuItem key={value} value={value}>{value}</MenuItem>
          ))}
        </TextField>
        <Button
          variant="contained"
          startIcon={<CreateNewFolderOutlinedIcon />}
          disabled={saving || !form.name}
          onClick={() => dispatch(createDocumentFolder(form)).then((result) => {
            if (!result.error) {
              setForm(initialForm);
            }
          })}
        >
          Create
        </Button>
      </Stack>

      <AppDataTable
        title="Folders"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...foldersPagination,
          onPageChange: (page) => dispatch(fetchDocumentFolders({ per_page: 50, page })),
        }}
        filters={[]}
        columns={[
          { key: 'name', header: 'Name' },
          { key: 'code', header: 'Code' },
          { key: 'parent_name', header: 'Parent', render: (row) => row.parent?.name || 'Root' },
          { key: 'visibility', header: 'Visibility' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button size="small" onClick={() => dispatch(updateDocumentFolder({ id: row.id, payload: { visibility: row.visibility === 'private' ? 'internal' : 'private' } }))}>
                  Toggle Visibility
                </Button>
                <Button size="small" color="error" startIcon={<DeleteOutlineOutlinedIcon />} onClick={() => dispatch(deleteDocumentFolder(row.id))}>
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
