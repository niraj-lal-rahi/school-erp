import { Alert, Button, Grid, Paper, Stack, Switch, TextField, Typography } from '@mui/material';
import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { CommunicationPageShell } from '../components/CommunicationPageShell';
import { fetchPreferences, updatePreference } from '../store/communicationSlice';

export function NotificationPreferencesPage() {
  const dispatch = useAppDispatch();
  const { preferences, loading, error } = useAppSelector((state) => state.communication);

  useEffect(() => {
    dispatch(fetchPreferences({ per_page: 25 }));
  }, [dispatch]);

  return (
    <CommunicationPageShell
      title="Notification Preferences"
      description="Respect recipient delivery choices, quiet hours, and channel preferences before the system attempts outreach."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <Grid container spacing={3}>
        {preferences.map((preference) => (
          <Grid key={preference.id} size={{ xs: 12, lg: 6 }}>
            <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
              <Stack spacing={2}>
                <Stack spacing={0.5}>
                  <Typography variant="h6">
                    {preference.user_type} #{preference.user_id}
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    Toggle allowed channels and define quiet hours for this recipient.
                  </Typography>
                </Stack>

                <Stack direction="row" spacing={2} flexWrap="wrap">
                  <Stack direction="row" alignItems="center" spacing={1}>
                    <Typography variant="body2">Email</Typography>
                    <Switch
                      checked={Boolean(preference.email_enabled)}
                      onChange={(event) => dispatch(updatePreference({
                        id: preference.id,
                        payload: { email_enabled: event.target.checked },
                      }))}
                    />
                  </Stack>
                  <Stack direction="row" alignItems="center" spacing={1}>
                    <Typography variant="body2">SMS</Typography>
                    <Switch
                      checked={Boolean(preference.sms_enabled)}
                      onChange={(event) => dispatch(updatePreference({
                        id: preference.id,
                        payload: { sms_enabled: event.target.checked },
                      }))}
                    />
                  </Stack>
                  <Stack direction="row" alignItems="center" spacing={1}>
                    <Typography variant="body2">Push</Typography>
                    <Switch
                      checked={Boolean(preference.push_enabled)}
                      onChange={(event) => dispatch(updatePreference({
                        id: preference.id,
                        payload: { push_enabled: event.target.checked },
                      }))}
                    />
                  </Stack>
                  <Stack direction="row" alignItems="center" spacing={1}>
                    <Typography variant="body2">In-App</Typography>
                    <Switch
                      checked={Boolean(preference.in_app_enabled)}
                      onChange={(event) => dispatch(updatePreference({
                        id: preference.id,
                        payload: { in_app_enabled: event.target.checked },
                      }))}
                    />
                  </Stack>
                </Stack>

                <Grid container spacing={2}>
                  <Grid size={{ xs: 12, md: 6 }}>
                    <TextField
                      fullWidth
                      label="Quiet Hours Start"
                      type="time"
                      InputLabelProps={{ shrink: true }}
                      value={preference.quiet_hours_start || ''}
                      onChange={(event) => dispatch(updatePreference({
                        id: preference.id,
                        payload: { quiet_hours_start: event.target.value || null },
                      }))}
                    />
                  </Grid>
                  <Grid size={{ xs: 12, md: 6 }}>
                    <TextField
                      fullWidth
                      label="Quiet Hours End"
                      type="time"
                      InputLabelProps={{ shrink: true }}
                      value={preference.quiet_hours_end || ''}
                      onChange={(event) => dispatch(updatePreference({
                        id: preference.id,
                        payload: { quiet_hours_end: event.target.value || null },
                      }))}
                    />
                  </Grid>
                </Grid>

                <Button variant="outlined" disabled={loading}>
                  Preferences auto-save on change
                </Button>
              </Stack>
            </Paper>
          </Grid>
        ))}
        {!preferences.length && !loading ? (
          <Typography color="text.secondary">No notification preferences are available yet.</Typography>
        ) : null}
      </Grid>
    </CommunicationPageShell>
  );
}
