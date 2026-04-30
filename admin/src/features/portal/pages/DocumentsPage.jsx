import { Alert } from '@mui/material';
import { useEffect } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch } from '../../../hooks/redux';
import { PortalPageShell } from '../components/PortalPageShell';
import { usePortalContext } from '../hooks/usePortalContext';
import { fetchPortalContext, fetchPortalDocuments } from '../store/portalSlice';

export function DocumentsPage() {
  const dispatch = useAppDispatch();
  const { activeStudentId, documents, sectionLoading, error } = usePortalContext();

  useEffect(() => {
    dispatch(fetchPortalContext());
  }, [dispatch]);

  useEffect(() => {
    if (activeStudentId) {
      dispatch(fetchPortalDocuments(activeStudentId));
    }
  }, [activeStudentId, dispatch]);

  return (
    <PortalPageShell
      title="Documents"
      description="Surface uploaded student documents and verification details in a clean read-only portal view."
      modeLabel="Documents"
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <AppDataTable
        title="Student Documents"
        columns={[
          { key: 'title', header: 'Title' },
          { key: 'document_type', header: 'Type' },
          { key: 'file_name', header: 'File Name' },
          { key: 'issued_date', header: 'Issued' },
          { key: 'expiry_date', header: 'Expires' },
          { key: 'verification_status', header: 'Verification' },
        ]}
        rows={documents?.documents || []}
        loading={sectionLoading}
        searchValue=""
        onSearchChange={() => {}}
        emptyState="No documents are available for the active student."
      />
    </PortalPageShell>
  );
}
