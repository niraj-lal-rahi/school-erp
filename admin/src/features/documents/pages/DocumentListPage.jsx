import CloudDownloadOutlinedIcon from '@mui/icons-material/CloudDownloadOutlined';
import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Alert, Button, Chip, Stack } from '@mui/material';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { documentsApi } from '../services/documentsApi';
import { DocumentStatusChip } from '../components/DocumentStatusChip';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import { fetchDocuments } from '../store/documentsSlice';

function downloadBlob(blob, fileName) {
  const url = window.URL.createObjectURL(blob);
  const anchor = window.document.createElement('a');
  anchor.href = url;
  anchor.download = fileName;
  anchor.click();
  window.URL.revokeObjectURL(url);
}

export function DocumentListPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { documents, documentsPagination, loading, error } = useAppSelector((state) => state.documents);
  const [search, setSearch] = useState('');
  const [filters, setFilters] = useState({
    owner_type: '',
    verification_status: '',
    status: '',
  });

  useEffect(() => {
    dispatch(fetchDocuments({ ...filters, per_page: 20, page: documentsPagination.page }));
  }, [dispatch, documentsPagination.page, filters]);

  const rows = documents.filter((item) => {
    if (!search) {
      return true;
    }

    return `${item.title} ${item.document_no || ''}`.toLowerCase().includes(search.toLowerCase());
  });

  return (
    <DocumentsPageShell
      title="Document Repository"
      description="Browse the shared document repository with filters for owners, verification state, and repository status."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <AppDataTable
        title="Documents"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={{
          ...documentsPagination,
          onPageChange: (page) => dispatch(fetchDocuments({ ...filters, page, per_page: 20 })),
        }}
        filters={[
          {
            key: 'owner_type',
            label: 'Owner',
            value: filters.owner_type,
            onChange: (value) => setFilters((current) => ({ ...current, owner_type: value })),
            options: [
              { label: 'All Owners', value: '' },
              ...['student', 'staff', 'guardian', 'tenant', 'user', 'general'].map((value) => ({ label: value, value })),
            ],
          },
          {
            key: 'verification_status',
            label: 'Verification',
            value: filters.verification_status,
            onChange: (value) => setFilters((current) => ({ ...current, verification_status: value })),
            options: [
              { label: 'All Verification States', value: '' },
              ...['pending', 'verified', 'rejected', 'expired'].map((value) => ({ label: value, value })),
            ],
          },
          {
            key: 'status',
            label: 'Status',
            value: filters.status,
            onChange: (value) => setFilters((current) => ({ ...current, status: value })),
            options: [
              { label: 'All Statuses', value: '' },
              ...['active', 'archived', 'deleted'].map((value) => ({ label: value, value })),
            ],
          },
        ]}
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'document_no', header: 'Document No' },
          { key: 'owner_type', header: 'Owner', render: (row) => <Chip size="small" label={row.owner_type || 'general'} /> },
          { key: 'category_name', header: 'Category', render: (row) => row.category?.name || 'Uncategorized' },
          { key: 'verification_status', header: 'Verification', render: (row) => <DocumentStatusChip value={row.verification_status} /> },
          { key: 'status', header: 'Status', render: (row) => <DocumentStatusChip value={row.status} /> },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button size="small" startIcon={<VisibilityOutlinedIcon />} onClick={() => navigate(`/documents/${row.id}`)}>
                  View
                </Button>
                <Button
                  size="small"
                  startIcon={<CloudDownloadOutlinedIcon />}
                  onClick={async () => {
                    const response = await documentsApi.downloadDocument(row.id);
                    const fileName = row.current_file?.original_file_name || `${row.title}.bin`;
                    downloadBlob(response.data, fileName);
                  }}
                >
                  Download
                </Button>
              </Stack>
            ),
          },
        ]}
      />
    </DocumentsPageShell>
  );
}
