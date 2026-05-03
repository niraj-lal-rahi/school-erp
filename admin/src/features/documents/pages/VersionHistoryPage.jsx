import UploadFileOutlinedIcon from '@mui/icons-material/UploadFileOutlined';
import { Alert, Button, Paper, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import { fetchDocumentVersions, uploadDocumentVersion } from '../store/documentsSlice';

export function VersionHistoryPage() {
  const { documentId } = useParams();
  const dispatch = useAppDispatch();
  const { versions, versionsPagination, loading, saving, error } = useAppSelector((state) => state.documents);
  const [file, setFile] = useState(null);

  useEffect(() => {
    if (documentId) {
      dispatch(fetchDocumentVersions(documentId));
    }
  }, [dispatch, documentId]);

  return (
    <DocumentsPageShell
      title="Version History"
      description="Track every file revision, keep one current file active, and add fresh versions without losing the audit trail."
      actions={
        <Button
          component="label"
          variant="contained"
          startIcon={<UploadFileOutlinedIcon />}
          disabled={saving}
        >
          {file ? file.name : 'Upload New Version'}
          <input
            hidden
            type="file"
            onChange={async (event) => {
              const nextFile = event.target.files?.[0] || null;
              setFile(nextFile);
              if (nextFile && documentId) {
                const payload = new FormData();
                payload.append('file', nextFile);
                await dispatch(uploadDocumentVersion({ id: documentId, payload }));
                dispatch(fetchDocumentVersions(documentId));
              }
            }}
          />
        </Button>
      }
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ border: '1px solid rgba(20,33,61,0.08)' }}>
        <AppDataTable
          title="Versions"
          rows={versions}
          loading={loading}
          searchValue=""
          onSearchChange={() => {}}
        pagination={{
          ...versionsPagination,
          onPageChange: () => dispatch(fetchDocumentVersions(documentId)),
        }}
          filters={[]}
          columns={[
            { key: 'version_no', header: 'Version' },
            { key: 'original_file_name', header: 'File Name' },
            { key: 'disk', header: 'Disk' },
            { key: 'mime_type', header: 'Mime Type' },
            { key: 'file_size', header: 'Size' },
            { key: 'is_current', header: 'Current', render: (row) => (row.is_current ? 'Yes' : 'No') },
            { key: 'created_at', header: 'Uploaded At' },
          ]}
        />
      </Paper>
    </DocumentsPageShell>
  );
}
