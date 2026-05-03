import CloudUploadOutlinedIcon from '@mui/icons-material/CloudUploadOutlined';
import { Alert, Button, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentStatusChip } from '../components/DocumentStatusChip';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import { createDocumentBulkUpload, fetchDocumentBulkUpload } from '../store/documentsSlice';

export function BulkUploadPage() {
  const dispatch = useAppDispatch();
  const { selectedBulkUpload, saving, error } = useAppSelector((state) => state.documents);
  const [uploadType, setUploadType] = useState('student');
  const [file, setFile] = useState(null);

  return (
    <DocumentsPageShell
      title="Bulk Upload"
      description="Lay down the operational foundation for high-volume document onboarding without giving up queue safety or auditability."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2}>
          <TextField select label="Upload Type" value={uploadType} onChange={(event) => setUploadType(event.target.value)} sx={{ maxWidth: 240 }}>
            {['student', 'staff', 'general'].map((value) => (
              <MenuItem key={value} value={value}>{value}</MenuItem>
            ))}
          </TextField>
          <Button component="label" variant="outlined" startIcon={<CloudUploadOutlinedIcon />}>
            {file ? file.name : 'Choose archive or spreadsheet'}
            <input hidden type="file" onChange={(event) => setFile(event.target.files?.[0] || null)} />
          </Button>
          <Stack direction="row" spacing={2}>
            <Button
              variant="contained"
              disabled={saving || !file}
              onClick={async () => {
                const payload = new FormData();
                payload.append('upload_type', uploadType);
                payload.append('file', file);
                const result = await dispatch(createDocumentBulkUpload(payload));
                if (!result.error) {
                  dispatch(fetchDocumentBulkUpload(result.payload.id));
                }
              }}
            >
              Start Bulk Upload
            </Button>
            {selectedBulkUpload ? (
              <Button onClick={() => dispatch(fetchDocumentBulkUpload(selectedBulkUpload.id))}>
                Refresh Status
              </Button>
            ) : null}
          </Stack>
        </Stack>
      </Paper>

      {selectedBulkUpload ? (
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack spacing={1}>
            <Typography variant="h6">Bulk Upload Status</Typography>
            <DocumentStatusChip value={selectedBulkUpload.status} />
            <Typography variant="body2">Total files: {selectedBulkUpload.total_files}</Typography>
            <Typography variant="body2">Success count: {selectedBulkUpload.success_count}</Typography>
            <Typography variant="body2">Failed count: {selectedBulkUpload.failed_count}</Typography>
            <Typography variant="body2" color="text.secondary">{selectedBulkUpload.error_log || 'No processing errors logged.'}</Typography>
          </Stack>
        </Paper>
      ) : null}
    </DocumentsPageShell>
  );
}
