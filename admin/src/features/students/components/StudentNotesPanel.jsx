import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import NoteAddOutlinedIcon from '@mui/icons-material/NoteAddOutlined';
import {
  Alert,
  Button,
  Divider,
  IconButton,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useState } from 'react';
import { PermissionGate } from '../../../components/common/PermissionGate';

export function StudentNotesPanel({ notes = [], onCreate, onDelete, saving, error }) {
  const [note, setNote] = useState('');
  const [visibilityType, setVisibilityType] = useState('internal');

  function handleSubmit(event) {
    event.preventDefault();
    if (!note.trim()) {
      return;
    }

    onCreate({
      note: note.trim(),
      visibility_type: visibilityType,
    });
    setNote('');
    setVisibilityType('internal');
  }

  return (
    <Stack spacing={3}>
      <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Stack spacing={0.5}>
            <Typography variant="h6">Internal Notes</Typography>
            <Typography variant="body2" color="text.secondary">
              Capture internal remarks for staff follow-up and continuity.
            </Typography>
          </Stack>

          {error ? <Alert severity="error">{error}</Alert> : null}

          <TextField
            select
            label="Visibility"
            value={visibilityType}
            onChange={(event) => setVisibilityType(event.target.value)}
          >
            <MenuItem value="internal">Internal</MenuItem>
            <MenuItem value="private">Private</MenuItem>
            <MenuItem value="admin_only">Admin Only</MenuItem>
          </TextField>

          <TextField
            multiline
            minRows={4}
            label="Note"
            value={note}
            onChange={(event) => setNote(event.target.value)}
          />

          <Stack direction="row" justifyContent="flex-end">
            <Button type="submit" variant="contained" startIcon={<NoteAddOutlinedIcon />} disabled={saving || !note.trim()}>
              {saving ? 'Saving...' : 'Add Note'}
            </Button>
          </Stack>
        </Stack>
      </Paper>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <Typography variant="h6">Note Timeline</Typography>
          {notes.length === 0 ? <Alert severity="info">No internal notes recorded yet.</Alert> : null}

          {notes.map((item, index) => (
            <Stack key={item.id} spacing={1.25}>
              {index > 0 ? <Divider /> : null}
              <Stack direction="row" justifyContent="space-between" spacing={2}>
                <Stack spacing={0.75}>
                  <Typography variant="body1">{item.note}</Typography>
                  <Typography variant="caption" color="text.secondary">
                    {item.visibility_type} • {item.created_by_name || 'Unknown'} • {item.created_at}
                  </Typography>
                </Stack>
                <PermissionGate permission="students.update" fallback={null}>
                  <IconButton color="error" onClick={() => onDelete(item.id)} disabled={saving}>
                    <DeleteOutlineOutlinedIcon />
                  </IconButton>
                </PermissionGate>
              </Stack>
            </Stack>
          ))}
        </Stack>
      </Paper>
    </Stack>
  );
}
