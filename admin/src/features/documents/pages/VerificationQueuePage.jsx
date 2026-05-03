import CheckCircleOutlineOutlinedIcon from '@mui/icons-material/CheckCircleOutlineOutlined';
import HighlightOffOutlinedIcon from '@mui/icons-material/HighlightOffOutlined';
import { Alert, Button, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentStatusChip } from '../components/DocumentStatusChip';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import {
  fetchPendingDocumentVerifications,
  rejectDocument,
  verifyDocument,
} from '../store/documentsSlice';

export function VerificationQueuePage() {
  const dispatch = useAppDispatch();
  const { pendingVerifications, verificationPagination, loading, saving, error } = useAppSelector((state) => state.documents);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchPendingDocumentVerifications({ per_page: 20, page: verificationPagination.page }));
  }, [dispatch, verificationPagination.page]);

  const rows = pendingVerifications.filter((item) => (`${item.title || ''} ${item.document_no || ''}`).toLowerCase().includes(search.toLowerCase()));

  return (
    <DocumentsPageShell
      title="Verification Queue"
      description="Keep compliance moving by reviewing pending documents, approving clean records, and rejecting the ones that need resubmission."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Pending Verification"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...verificationPagination,
          onPageChange: (page) => dispatch(fetchPendingDocumentVerifications({ per_page: 20, page })),
        }}
        filters={[]}
        columns={[
          { key: 'title', header: 'Document' },
          { key: 'document_no', header: 'Document No' },
          { key: 'owner_type', header: 'Owner' },
          { key: 'category_name', header: 'Category', render: (row) => row.category?.name || 'Uncategorized' },
          { key: 'verification_status', header: 'Status', render: (row) => <DocumentStatusChip value={row.verification_status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button
                  size="small"
                  variant="contained"
                  startIcon={<CheckCircleOutlineOutlinedIcon />}
                  disabled={saving}
                  onClick={() => dispatch(verifyDocument({ id: row.id, payload: { remarks: 'Verified from queue.' } }))}
                >
                  Approve
                </Button>
                <Button
                  size="small"
                  color="error"
                  startIcon={<HighlightOffOutlinedIcon />}
                  disabled={saving}
                  onClick={() => dispatch(rejectDocument({ id: row.id, payload: { remarks: 'Rejected from queue.' } }))}
                >
                  Reject
                </Button>
              </Stack>
            ),
          },
        ]}
      />
    </DocumentsPageShell>
  );
}
