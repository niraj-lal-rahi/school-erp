import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, Grid, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';

const initialForm = {
  document_type: '',
  title: '',
  issued_by: '',
  issued_date: '',
  expiry_date: '',
  verification_status: '',
  remarks: '',
  file: null,
};

export function StaffDocumentsTab({ documents, saving, error, onUpload, onDelete }) {
  const [form, setForm] = useState(initialForm);

  async function handleSubmit(event) {
    event.preventDefault();
    const payload = new FormData();
    Object.entries(form).forEach(([key, value]) => {
      if (value !== '' && value !== null) {
        payload.append(key, value);
      }
    });

    const result = await onUpload(payload);
    if (!result?.error) {
      setForm(initialForm);
    }
  }

  return (
    <Grid container spacing={3}>
      <Grid size={{ xs: 12, lg: 8 }}>
        <AppDataTable
          title="Staff Documents"
          columns={[
            { key: 'title', header: 'Title' },
            { key: 'document_type', header: 'Type' },
            { key: 'file_name', header: 'File' },
            { key: 'verification_status', header: 'Verification' },
            { key: 'issued_date', header: 'Issued' },
            {
              key: 'actions',
              header: 'Actions',
              render: (row) => (
                <IconButton color="error" onClick={() => onDelete(row.id)} disabled={saving}>
                  <DeleteOutlineOutlinedIcon />
                </IconButton>
              ),
            },
          ]}
          rows={documents}
          loading={false}
          searchValue=""
          onSearchChange={() => {}}
          emptyState="No staff documents uploaded yet."
        />
      </Grid>

      <Grid size={{ xs: 12, lg: 4 }}>
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack component="form" spacing={2} onSubmit={handleSubmit}>
            <Typography variant="h6">Upload Document</Typography>
            {error ? <Alert severity="error">{error}</Alert> : null}
            <TextField label="Document Type" value={form.document_type} onChange={(event) => setForm((current) => ({ ...current, document_type: event.target.value }))} />
            <TextField label="Title" value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} />
            <TextField label="Issued By" value={form.issued_by} onChange={(event) => setForm((current) => ({ ...current, issued_by: event.target.value }))} />
            <TextField type="date" label="Issued Date" value={form.issued_date} onChange={(event) => setForm((current) => ({ ...current, issued_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
            <TextField type="date" label="Expiry Date" value={form.expiry_date} onChange={(event) => setForm((current) => ({ ...current, expiry_date: event.target.value }))} InputLabelProps={{ shrink: true }} />
            <TextField select label="Verification Status" value={form.verification_status} onChange={(event) => setForm((current) => ({ ...current, verification_status: event.target.value }))}>
              {['', 'pending', 'verified', 'rejected'].map((status) => (
                <MenuItem key={status || 'blank'} value={status}>
                  {status || 'Select'}
                </MenuItem>
              ))}
            </TextField>
            <TextField multiline minRows={3} label="Remarks" value={form.remarks} onChange={(event) => setForm((current) => ({ ...current, remarks: event.target.value }))} />
            <Button variant="outlined" component="label">
              {form.file ? form.file.name : 'Choose File'}
              <input hidden type="file" onChange={(event) => setForm((current) => ({ ...current, file: event.target.files?.[0] || null }))} />
            </Button>
            <Button type="submit" variant="contained" disabled={saving || !form.file}>
              {saving ? 'Uploading...' : 'Upload Document'}
            </Button>
          </Stack>
        </Paper>
      </Grid>
    </Grid>
  );
}
