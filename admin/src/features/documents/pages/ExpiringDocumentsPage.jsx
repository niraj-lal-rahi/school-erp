import { Alert, Grid } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentStatusChip } from '../components/DocumentStatusChip';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import { fetchExpiredDocuments, fetchExpiringDocuments } from '../store/documentsSlice';

export function ExpiringDocumentsPage() {
  const dispatch = useAppDispatch();
  const { reports, expiringPagination, expiredPagination, loading, error } = useAppSelector((state) => state.documents);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchExpiringDocuments({ per_page: 20, page: expiringPagination.page }));
    dispatch(fetchExpiredDocuments({ per_page: 20, page: expiredPagination.page }));
  }, [dispatch, expiringPagination.page, expiredPagination.page]);

  const expiringRows = reports.expiring.filter((item) => (`${item.title || ''} ${item.document_no || ''}`).toLowerCase().includes(search.toLowerCase()));
  const expiredRows = reports.expired.filter((item) => (`${item.title || ''} ${item.document_no || ''}`).toLowerCase().includes(search.toLowerCase()));

  return (
    <DocumentsPageShell
      title="Expiring Documents"
      description="Spot risk early, separate soon-to-expire records from already expired ones, and keep renewal workflows moving."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12 }}>
          <AppDataTable
            title="Expiring Soon"
            rows={expiringRows}
            loading={loading}
            searchValue={search}
            onSearchChange={setSearch}
            pagination={{
              ...expiringPagination,
              onPageChange: (page) => dispatch(fetchExpiringDocuments({ per_page: 20, page })),
            }}
            filters={[]}
            columns={[
              { key: 'title', header: 'Document' },
              { key: 'document_no', header: 'Document No' },
              { key: 'owner_type', header: 'Owner' },
              { key: 'expiry_date', header: 'Expiry Date' },
              { key: 'verification_status', header: 'Status', render: (row) => <DocumentStatusChip value={row.verification_status} /> },
            ]}
          />
        </Grid>
        <Grid size={{ xs: 12 }}>
          <AppDataTable
            title="Expired Documents"
            rows={expiredRows}
            loading={loading}
            searchValue=""
            onSearchChange={() => {}}
            pagination={{
              ...expiredPagination,
              onPageChange: (page) => dispatch(fetchExpiredDocuments({ per_page: 20, page })),
            }}
            filters={[]}
            columns={[
              { key: 'title', header: 'Document' },
              { key: 'document_no', header: 'Document No' },
              { key: 'owner_type', header: 'Owner' },
              { key: 'expiry_date', header: 'Expiry Date' },
              { key: 'verification_status', header: 'Status', render: (row) => <DocumentStatusChip value={row.verification_status} /> },
            ]}
          />
        </Grid>
      </Grid>
    </DocumentsPageShell>
  );
}
