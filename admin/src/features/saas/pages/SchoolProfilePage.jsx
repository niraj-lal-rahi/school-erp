import { Alert, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { fetchTenant } from '../store/saasSlice';
import { SaasPageShell } from '../components/SaasPageShell';
import { useSaasAccess } from '../hooks/useSaasAccess';

export function SchoolProfilePage() {
  const dispatch = useAppDispatch();
  const { user } = useSaasAccess();
  const { selectedTenant, error } = useAppSelector((state) => state.saas);

  useEffect(() => {
    if (user?.school_id) {
      dispatch(fetchTenant(user.school_id));
    }
  }, [dispatch, user]);

  return (
    <SaasPageShell
      title="School Profile"
      description="A tenant-admin friendly snapshot of the current school record, useful for verifying customer-facing identity and billing metadata."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={1.5}>
          <Typography variant="h5">{selectedTenant?.name || 'Current School'}</Typography>
          <Typography variant="body2" color="text.secondary">Email: {selectedTenant?.email || 'N/A'}</Typography>
          <Typography variant="body2" color="text.secondary">Phone: {selectedTenant?.phone || 'N/A'}</Typography>
          <Typography variant="body2" color="text.secondary">Address: {selectedTenant?.address || 'Not configured'}</Typography>
          <Typography variant="body2" color="text.secondary">Code: {selectedTenant?.code || 'N/A'}</Typography>
          <Typography variant="body2" color="text.secondary">Subdomain: {selectedTenant?.subdomain || 'N/A'}</Typography>
        </Stack>
      </Paper>
    </SaasPageShell>
  );
}
