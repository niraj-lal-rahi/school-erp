import UploadFileOutlinedIcon from '@mui/icons-material/UploadFileOutlined';
import { Alert, Button, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import { fetchDocumentCategories, fetchDocumentFolders, fetchDocumentTags, createDocument } from '../store/documentsSlice';

const initialForm = {
  title: '',
  description: '',
  category_id: '',
  folder_id: '',
  owner_type: 'general',
  owner_id: '',
  document_no: '',
  issue_date: '',
  expiry_date: '',
};

export function UploadDocumentPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { categories, folders, tags, saving, error } = useAppSelector((state) => state.documents);
  const [form, setForm] = useState(initialForm);
  const [selectedTagIds, setSelectedTagIds] = useState([]);
  const [file, setFile] = useState(null);

  useEffect(() => {
    dispatch(fetchDocumentCategories({ per_page: 100 }));
    dispatch(fetchDocumentFolders({ per_page: 100 }));
    dispatch(fetchDocumentTags({ per_page: 100 }));
  }, [dispatch]);

  const folderOptions = useMemo(() => folders.map((folder) => ({ label: folder.name, value: folder.id })), [folders]);

  const handleSubmit = async () => {
    const payload = new FormData();
    Object.entries(form).forEach(([key, value]) => {
      if (value !== '' && value !== null && value !== undefined) {
        payload.append(key, value);
      }
    });
    selectedTagIds.forEach((tagId, index) => payload.append(`tag_ids[${index}]`, tagId));
    if (file) {
      payload.append('file', file);
    }

    const result = await dispatch(createDocument(payload));
    if (!result.error) {
      navigate(`/documents/${result.payload.id}`);
    }
  };

  return (
    <DocumentsPageShell
      title="Upload Document"
      description="Capture secure metadata, tag the record correctly, and store the file behind private Laravel Storage paths."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Grid container spacing={3}>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Title" value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} />
          </Grid>
          <Grid size={{ xs: 12, md: 6 }}>
            <TextField fullWidth label="Document No" value={form.document_no} onChange={(event) => setForm((current) => ({ ...current, document_no: event.target.value }))} />
          </Grid>
          <Grid size={{ xs: 12 }}>
            <TextField fullWidth multiline minRows={3} label="Description" value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField select fullWidth label="Category" value={form.category_id} onChange={(event) => setForm((current) => ({ ...current, category_id: event.target.value }))}>
              <MenuItem value="">No Category</MenuItem>
              {categories.map((category) => (
                <MenuItem key={category.id} value={category.id}>{category.name}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField select fullWidth label="Folder" value={form.folder_id} onChange={(event) => setForm((current) => ({ ...current, folder_id: event.target.value }))}>
              <MenuItem value="">Root Level</MenuItem>
              {folderOptions.map((folder) => (
                <MenuItem key={folder.value} value={folder.value}>{folder.label}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField select fullWidth label="Owner Type" value={form.owner_type} onChange={(event) => setForm((current) => ({ ...current, owner_type: event.target.value }))}>
              {['student', 'staff', 'guardian', 'tenant', 'user', 'general'].map((value) => (
                <MenuItem key={value} value={value}>{value}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth label="Owner ID" value={form.owner_id} onChange={(event) => setForm((current) => ({ ...current, owner_id: event.target.value }))} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth type="date" label="Issue Date" InputLabelProps={{ shrink: true }} value={form.issue_date} onChange={(event) => setForm((current) => ({ ...current, issue_date: event.target.value }))} />
          </Grid>
          <Grid size={{ xs: 12, md: 4 }}>
            <TextField fullWidth type="date" label="Expiry Date" InputLabelProps={{ shrink: true }} value={form.expiry_date} onChange={(event) => setForm((current) => ({ ...current, expiry_date: event.target.value }))} />
          </Grid>
          <Grid size={{ xs: 12 }}>
            <TextField
              select
              fullWidth
              label="Tags"
              value={selectedTagIds}
              onChange={(event) => setSelectedTagIds(typeof event.target.value === 'string' ? event.target.value.split(',') : event.target.value)}
              SelectProps={{ multiple: true }}
            >
              {tags.map((tag) => (
                <MenuItem key={tag.id} value={tag.id}>{tag.name}</MenuItem>
              ))}
            </TextField>
          </Grid>
          <Grid size={{ xs: 12 }}>
            <Stack spacing={1}>
              <Typography variant="subtitle2">File</Typography>
              <Button component="label" variant="outlined" startIcon={<UploadFileOutlinedIcon />}>
                {file ? file.name : 'Choose file'}
                <input hidden type="file" onChange={(event) => setFile(event.target.files?.[0] || null)} />
              </Button>
            </Stack>
          </Grid>
          <Grid size={{ xs: 12 }}>
            <Stack direction="row" justifyContent="flex-end">
              <Button variant="contained" disabled={saving || !form.title || !file} onClick={handleSubmit}>
                Upload Document
              </Button>
            </Stack>
          </Grid>
        </Grid>
      </Paper>
    </DocumentsPageShell>
  );
}
