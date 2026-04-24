import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, IconButton, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';

export function StaffNotesTab({ items, saving, error, onCreate, onDelete }) {
  const [form, setForm] = useState({
    note: '',
    visibility_type: 'internal',
  });

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await onCreate(form);
    if (!result?.error) {
      setForm({ note: '', visibility_type: 'internal' });
    }
  }

  return (
    <Stack spacing={3}>
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack component="form" spacing={2} onSubmit={handleSubmit}>
          <Typography variant="h6">Internal Note</Typography>
          {error ? <Alert severity="error">{error}</Alert> : null}
          <TextField select label="Visibility" value={form.visibility_type} onChange={(event) => setForm((current) => ({ ...current, visibility_type: event.target.value }))}>
            {['internal', 'private', 'admin_only'].map((item) => (
              <MenuItem key={item} value={item}>{item}</MenuItem>
            ))}
          </TextField>
          <TextField multiline minRows={4} label="Note" value={form.note} onChange={(event) => setForm((current) => ({ ...current, note: event.target.value }))} />
          <Button type="submit" variant="contained" disabled={saving}>{saving ? 'Saving...' : 'Add Note'}</Button>
        </Stack>
      </Paper>

      <AppDataTable
        title="HR Notes"
        columns={[
          { key: 'created_at', header: 'Created' },
          { key: 'visibility_type', header: 'Visibility' },
          { key: 'note', header: 'Note' },
          { key: 'creator', header: 'Created By', render: (row) => row.creator?.name || 'System' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <IconButton color="error" onClick={() => onDelete(row.id)}>
                <DeleteOutlineOutlinedIcon />
              </IconButton>
            ),
          },
        ]}
        rows={items}
        loading={false}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No HR notes saved yet."
      />
    </Stack>
  );
}
