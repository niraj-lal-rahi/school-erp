import DescriptionOutlinedIcon from '@mui/icons-material/DescriptionOutlined';
import FolderOutlinedIcon from '@mui/icons-material/FolderOutlined';
import SecurityOutlinedIcon from '@mui/icons-material/SecurityOutlined';
import WarningAmberOutlinedIcon from '@mui/icons-material/WarningAmberOutlined';
import { Alert, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import {
  fetchDocumentCategories,
  fetchDocumentFolders,
  fetchDocuments,
  fetchDocumentStorageUsageReport,
  fetchPendingDocumentVerifications,
} from '../store/documentsSlice';

function StatCard({ icon, label, value, helper }) {
  return (
    <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
      <Stack spacing={1}>
        <Stack direction="row" spacing={1} alignItems="center">
          {icon}
          <Typography variant="subtitle2" color="text.secondary">
            {label}
          </Typography>
        </Stack>
        <Typography variant="h4">{value}</Typography>
        <Typography variant="body2" color="text.secondary">
          {helper}
        </Typography>
      </Stack>
    </Paper>
  );
}

export function DocumentDashboardPage() {
  const dispatch = useAppDispatch();
  const { categories, folders, documents, pendingVerifications, reports, error } = useAppSelector((state) => state.documents);

  useEffect(() => {
    dispatch(fetchDocumentCategories({ per_page: 50 }));
    dispatch(fetchDocumentFolders({ per_page: 50 }));
    dispatch(fetchDocuments({ per_page: 50 }));
    dispatch(fetchPendingDocumentVerifications({ per_page: 20 }));
    dispatch(fetchDocumentStorageUsageReport());
  }, [dispatch]);

  const storageUsed = reports.storageUsage?.used_mb ?? reports.storageUsage?.total_used_mb ?? 0;
  const storageLimit = reports.storageUsage?.limit_mb ?? reports.storageUsage?.allocated_mb ?? 'N/A';

  return (
    <DocumentsPageShell
      title="Document Dashboard"
      description="Keep an eye on repository growth, verification pressure, and the document structures people rely on every day."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <StatCard icon={<DescriptionOutlinedIcon color="primary" />} label="Documents" value={documents.length} helper="Active and recently synced repository records." />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <StatCard icon={<FolderOutlinedIcon color="success" />} label="Folders" value={folders.length} helper="Nested structures available for organized storage." />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <StatCard icon={<SecurityOutlinedIcon color="warning" />} label="Pending Verification" value={pendingVerifications.length} helper="Documents waiting for verifier attention." />
        </Grid>
        <Grid size={{ xs: 12, md: 6, xl: 3 }}>
          <StatCard icon={<WarningAmberOutlinedIcon color="error" />} label="Storage Usage" value={`${storageUsed} MB`} helper={`Current usage against ${storageLimit} MB available.`} />
        </Grid>
        <Grid size={{ xs: 12 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1}>
              <Typography variant="h6">Category Coverage</Typography>
              <Typography variant="body2" color="text.secondary">
                {categories.length} categories are ready for student, staff, finance, and general document capture. Use the category screen to tune verification and expiry behavior by document type.
              </Typography>
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </DocumentsPageShell>
  );
}
