import CloudDownloadOutlinedIcon from '@mui/icons-material/CloudDownloadOutlined';
import HistoryOutlinedIcon from '@mui/icons-material/HistoryOutlined';
import RestoreOutlinedIcon from '@mui/icons-material/RestoreOutlined';
import { Alert, Button, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { Link as RouterLink, useParams } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { documentsApi } from '../services/documentsApi';
import { DocumentStatusChip } from '../components/DocumentStatusChip';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import {
  fetchDocument,
  fetchDocumentAuditLogs,
  fetchDocumentPermissions,
  restoreDocument,
} from '../store/documentsSlice';

function downloadBlob(blob, fileName) {
  const url = window.URL.createObjectURL(blob);
  const anchor = window.document.createElement('a');
  anchor.href = url;
  anchor.download = fileName;
  anchor.click();
  window.URL.revokeObjectURL(url);
}

export function DocumentDetailPage() {
  const { documentId } = useParams();
  const dispatch = useAppDispatch();
  const { selectedDocument, permissions, auditLogs, loading, saving, error } = useAppSelector((state) => state.documents);

  useEffect(() => {
    if (!documentId) {
      return;
    }

    dispatch(fetchDocument(documentId));
    dispatch(fetchDocumentPermissions(documentId));
    dispatch(fetchDocumentAuditLogs(documentId));
  }, [dispatch, documentId]);

  return (
    <DocumentsPageShell
      title={selectedDocument?.title || 'Document Detail'}
      description="Review metadata, current file state, permissions, and the audit trail before taking action."
      actions={
        <Stack direction="row" spacing={1}>
          <Button component={RouterLink} to={`/documents/${documentId}/versions`} startIcon={<HistoryOutlinedIcon />}>
            Version History
          </Button>
          {selectedDocument ? (
            <Button
              startIcon={<CloudDownloadOutlinedIcon />}
              onClick={async () => {
                const response = await documentsApi.downloadDocument(selectedDocument.id);
                const fileName = selectedDocument.current_file?.original_file_name || `${selectedDocument.title}.bin`;
                downloadBlob(response.data, fileName);
              }}
            >
              Download
            </Button>
          ) : null}
          {selectedDocument?.status === 'deleted' ? (
            <Button
              variant="contained"
              startIcon={<RestoreOutlinedIcon />}
              disabled={saving}
              onClick={() => dispatch(restoreDocument(selectedDocument.id))}
            >
              Restore
            </Button>
          ) : null}
        </Stack>
      }
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      {loading || !selectedDocument ? null : (
        <Grid container spacing={3}>
          <Grid size={{ xs: 12, lg: 7 }}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={2}>
                <Typography variant="h6">Metadata</Typography>
                <Typography variant="body2" color="text.secondary">{selectedDocument.description || 'No description provided.'}</Typography>
                <Typography variant="body2">Document No: {selectedDocument.document_no || 'Not assigned'}</Typography>
                <Typography variant="body2">Owner: {selectedDocument.owner_type || 'general'} {selectedDocument.owner_id ? `#${selectedDocument.owner_id}` : ''}</Typography>
                <Typography variant="body2">Category: {selectedDocument.category?.name || 'Uncategorized'}</Typography>
                <Typography variant="body2">Folder: {selectedDocument.folder?.name || 'Root level'}</Typography>
                <Stack direction="row" spacing={1}>
                  <DocumentStatusChip value={selectedDocument.status} />
                  <DocumentStatusChip value={selectedDocument.verification_status} />
                </Stack>
              </Stack>
            </Paper>
          </Grid>
          <Grid size={{ xs: 12, lg: 5 }}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={2}>
                <Typography variant="h6">Current File</Typography>
                <Typography variant="body2">File name: {selectedDocument.current_file?.original_file_name || 'No file attached'}</Typography>
                <Typography variant="body2">Disk: {selectedDocument.current_file?.disk || 'private'}</Typography>
                <Typography variant="body2">Size: {selectedDocument.current_file?.file_size || 0} bytes</Typography>
                <Typography variant="body2">Checksum: {selectedDocument.current_file?.checksum || 'Pending checksum'}</Typography>
              </Stack>
            </Paper>
          </Grid>
          <Grid size={{ xs: 12, lg: 6 }}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={2}>
                <Typography variant="h6">Permissions</Typography>
                {permissions.length ? permissions.map((permission) => (
                  <Typography key={permission.id} variant="body2">
                    {permission.permission_type} {permission.permission_id ? `#${permission.permission_id}` : ''}: view {permission.can_view ? 'yes' : 'no'}, download {permission.can_download ? 'yes' : 'no'}, verify {permission.can_verify ? 'yes' : 'no'}
                  </Typography>
                )) : (
                  <Typography variant="body2" color="text.secondary">No explicit permissions stored for this document yet.</Typography>
                )}
              </Stack>
            </Paper>
          </Grid>
          <Grid size={{ xs: 12, lg: 6 }}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={2}>
                <Typography variant="h6">Audit Timeline</Typography>
                {auditLogs.length ? auditLogs.slice(0, 8).map((log) => (
                  <Typography key={log.id} variant="body2">
                    {log.action} by user #{log.performed_by || 'system'} at {log.created_at}
                  </Typography>
                )) : (
                  <Typography variant="body2" color="text.secondary">Audit activity will appear here as the document moves through review and access flows.</Typography>
                )}
              </Stack>
            </Paper>
          </Grid>
        </Grid>
      )}
    </DocumentsPageShell>
  );
}
