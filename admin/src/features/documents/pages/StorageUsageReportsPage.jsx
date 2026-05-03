import { Alert, Grid, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { DocumentsPageShell } from '../components/DocumentsPageShell';
import {
  fetchDocumentStorageUsageReport,
  fetchDocumentVerificationStatusReport,
} from '../store/documentsSlice';

export function StorageUsageReportsPage() {
  const dispatch = useAppDispatch();
  const { reports, error } = useAppSelector((state) => state.documents);

  useEffect(() => {
    dispatch(fetchDocumentStorageUsageReport());
    dispatch(fetchDocumentVerificationStatusReport());
  }, [dispatch]);

  const usage = reports.storageUsage || {};
  const verification = reports.verificationStatus || {};

  return (
    <DocumentsPageShell
      title="Storage & Verification Reports"
      description="See how the repository is growing and how verification workload is distributed across your tenant."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1}>
              <Typography variant="h6">Storage Usage</Typography>
              <Typography variant="body2">Used: {usage.used_mb ?? usage.total_used_mb ?? 0} MB</Typography>
              <Typography variant="body2">Limit: {usage.limit_mb ?? usage.allocated_mb ?? 'N/A'} MB</Typography>
              <Typography variant="body2">Files: {usage.files_count ?? usage.total_files ?? 'N/A'}</Typography>
            </Stack>
          </Paper>
        </Grid>
        <Grid size={{ xs: 12, md: 6 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={1}>
              <Typography variant="h6">Verification Mix</Typography>
              <Typography variant="body2">Pending: {verification.pending ?? 0}</Typography>
              <Typography variant="body2">Verified: {verification.verified ?? 0}</Typography>
              <Typography variant="body2">Rejected: {verification.rejected ?? 0}</Typography>
              <Typography variant="body2">Expired: {verification.expired ?? 0}</Typography>
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </DocumentsPageShell>
  );
}
