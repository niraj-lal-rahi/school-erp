import UploadFileOutlinedIcon from '@mui/icons-material/UploadFileOutlined';
import {
  Alert,
  Button,
  MenuItem,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useState } from 'react';

export function DocumentUploadPanel({ onUpload, saving, error, disabled }) {
  const [documentType, setDocumentType] = useState('birth_certificate');
  const [title, setTitle] = useState('');
  const [file, setFile] = useState(null);

  function handleSubmit(event) {
    event.preventDefault();

    if (!file || !title) {
      return;
    }

    const payload = new FormData();
    payload.append('document_type', documentType);
    payload.append('title', title);
    payload.append('file', file);

    onUpload(payload);
  }

  return (
    <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={2}>
        <Typography variant="h6">Upload Documents</Typography>
        <Typography variant="body2" color="text.secondary">
          Attach admission records, certificates, consent forms, or profile documents to the student file.
        </Typography>

        {error ? <Alert severity="error">{error}</Alert> : null}

        <TextField select label="Document Type" value={documentType} onChange={(e) => setDocumentType(e.target.value)}>
          <MenuItem value="birth_certificate">Birth Certificate</MenuItem>
          <MenuItem value="transfer_certificate">Transfer Certificate</MenuItem>
          <MenuItem value="medical_record">Medical Record</MenuItem>
          <MenuItem value="id_proof">ID Proof</MenuItem>
        </TextField>

        <TextField label="Title" value={title} onChange={(e) => setTitle(e.target.value)} />

        <Button variant="outlined" component="label" disabled={disabled}>
          Choose File
          <input hidden type="file" onChange={(e) => setFile(e.target.files?.[0] || null)} />
        </Button>

        <Typography variant="caption" color="text.secondary">
          {file ? file.name : 'No file selected'}
        </Typography>

        <Button type="submit" variant="contained" startIcon={<UploadFileOutlinedIcon />} disabled={disabled || !file || !title}>
          {saving ? 'Uploading...' : 'Upload Document'}
        </Button>
      </Stack>
    </Paper>
  );
}
