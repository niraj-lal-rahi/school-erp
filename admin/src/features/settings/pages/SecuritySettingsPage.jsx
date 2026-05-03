import SecurityOutlinedIcon from '@mui/icons-material/SecurityOutlined';
import { Alert, Button, FormControlLabel, Paper, Stack, Switch, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SettingsPageShell } from '../components/SettingsPageShell';
import { fetchSecurity, updateSecurity } from '../store/settingsSlice';

const initialForm = {
  password_min_length: 8,
  password_requires_uppercase: true,
  password_requires_number: true,
  password_requires_symbol: false,
  session_timeout_minutes: 120,
  max_login_attempts: 5,
  lockout_minutes: 15,
  two_factor_enabled: false,
};

export function SecuritySettingsPage() {
  const dispatch = useAppDispatch();
  const { security, loading, saving, error } = useAppSelector((state) => state.settings);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchSecurity());
  }, [dispatch]);

  useEffect(() => {
    if (security) {
      setForm({
        password_min_length: security.password_min_length ?? 8,
        password_requires_uppercase: security.password_requires_uppercase ?? true,
        password_requires_number: security.password_requires_number ?? true,
        password_requires_symbol: security.password_requires_symbol ?? false,
        session_timeout_minutes: security.session_timeout_minutes ?? 120,
        max_login_attempts: security.max_login_attempts ?? 5,
        lockout_minutes: security.lockout_minutes ?? 15,
        two_factor_enabled: security.two_factor_enabled ?? false,
      });
    }
  }, [security]);

  return (
    <SettingsPageShell
      title="Security Settings"
      description="Keep auth expectations visible and configurable with password rules, lockout controls, session timing, and two-factor posture."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2.5}>
          <Stack direction="row" spacing={1.5} alignItems="center">
            <SecurityOutlinedIcon color="primary" />
            <Typography variant="h6">Security Rules</Typography>
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField type="number" label="Password Min Length" value={form.password_min_length} onChange={(event) => setForm((current) => ({ ...current, password_min_length: Number(event.target.value) }))} fullWidth />
            <TextField type="number" label="Session Timeout (minutes)" value={form.session_timeout_minutes} onChange={(event) => setForm((current) => ({ ...current, session_timeout_minutes: Number(event.target.value) }))} fullWidth />
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField type="number" label="Max Login Attempts" value={form.max_login_attempts} onChange={(event) => setForm((current) => ({ ...current, max_login_attempts: Number(event.target.value) }))} fullWidth />
            <TextField type="number" label="Lockout Minutes" value={form.lockout_minutes} onChange={(event) => setForm((current) => ({ ...current, lockout_minutes: Number(event.target.value) }))} fullWidth />
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap">
            <FormControlLabel control={<Switch checked={form.password_requires_uppercase} onChange={(event) => setForm((current) => ({ ...current, password_requires_uppercase: event.target.checked }))} />} label="Require Uppercase" />
            <FormControlLabel control={<Switch checked={form.password_requires_number} onChange={(event) => setForm((current) => ({ ...current, password_requires_number: event.target.checked }))} />} label="Require Number" />
            <FormControlLabel control={<Switch checked={form.password_requires_symbol} onChange={(event) => setForm((current) => ({ ...current, password_requires_symbol: event.target.checked }))} />} label="Require Symbol" />
            <FormControlLabel control={<Switch checked={form.two_factor_enabled} onChange={(event) => setForm((current) => ({ ...current, two_factor_enabled: event.target.checked }))} />} label="Enable Two-Factor" />
          </Stack>

          <Button variant="contained" disabled={saving || loading} onClick={() => dispatch(updateSecurity(form))}>
            Save Security Settings
          </Button>
        </Stack>
      </Paper>
    </SettingsPageShell>
  );
}
