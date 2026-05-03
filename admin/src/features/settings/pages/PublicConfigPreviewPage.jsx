import VisibilityOutlinedIcon from '@mui/icons-material/VisibilityOutlined';
import { Alert, Paper, Stack, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SettingsPageShell } from '../components/SettingsPageShell';
import { fetchPublicConfig } from '../store/settingsSlice';

export function PublicConfigPreviewPage() {
  const dispatch = useAppDispatch();
  const { publicConfig, loading, error } = useAppSelector((state) => state.settings);

  useEffect(() => {
    dispatch(fetchPublicConfig());
  }, [dispatch]);

  return (
    <SettingsPageShell
      title="Public Config Preview"
      description="Inspect the frontend-safe config envelope exactly the way the app consumes it, with branding, locale, public settings, and enabled public features only."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction="row" spacing={1.5} alignItems="center" mb={2}>
          <VisibilityOutlinedIcon color="primary" />
          <Typography variant="h6">Safe Frontend Payload</Typography>
        </Stack>
        <Typography variant="body2" color="text.secondary" mb={2}>
          This view is useful for sanity-checking what the frontend really gets after masking, fallback, and public-scope filtering are applied.
        </Typography>
        {loading ? (
          <Typography variant="body2" color="text.secondary">Loading preview...</Typography>
        ) : (
          <pre style={{ margin: 0, whiteSpace: 'pre-wrap', fontSize: 12 }}>
            {JSON.stringify(publicConfig || {}, null, 2)}
          </pre>
        )}
      </Paper>
    </SettingsPageShell>
  );
}
