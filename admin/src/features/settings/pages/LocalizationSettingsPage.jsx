import TranslateOutlinedIcon from '@mui/icons-material/TranslateOutlined';
import { Alert, Button, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SettingsPageShell } from '../components/SettingsPageShell';
import { fetchLocalization, updateLocalization } from '../store/settingsSlice';

const initialForm = {
  timezone: 'Asia/Kolkata',
  locale: 'en',
  date_format: 'd-m-Y',
  time_format: 'h:i A',
  currency: 'INR',
  currency_symbol: '₹',
  first_day_of_week: 'monday',
};

export function LocalizationSettingsPage() {
  const dispatch = useAppDispatch();
  const { localization, loading, saving, error } = useAppSelector((state) => state.settings);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchLocalization());
  }, [dispatch]);

  useEffect(() => {
    if (localization) {
      setForm({
        timezone: localization.timezone || 'Asia/Kolkata',
        locale: localization.locale || 'en',
        date_format: localization.date_format || 'd-m-Y',
        time_format: localization.time_format || 'h:i A',
        currency: localization.currency || 'INR',
        currency_symbol: localization.currency_symbol || '₹',
        first_day_of_week: localization.first_day_of_week || 'monday',
      });
    }
  }, [localization]);

  return (
    <SettingsPageShell
      title="Localization Settings"
      description="Adjust how time, date, week flow, and money feel inside the tenant so the ERP matches local operational reality."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2.5}>
          <Stack direction="row" spacing={1.5} alignItems="center">
            <TranslateOutlinedIcon color="primary" />
            <Typography variant="h6">Locale & Time Preferences</Typography>
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField label="Timezone" value={form.timezone} onChange={(event) => setForm((current) => ({ ...current, timezone: event.target.value }))} fullWidth />
            <TextField label="Locale" value={form.locale} onChange={(event) => setForm((current) => ({ ...current, locale: event.target.value }))} fullWidth />
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField label="Date Format" value={form.date_format} onChange={(event) => setForm((current) => ({ ...current, date_format: event.target.value }))} fullWidth />
            <TextField label="Time Format" value={form.time_format} onChange={(event) => setForm((current) => ({ ...current, time_format: event.target.value }))} fullWidth />
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField label="Currency" value={form.currency} onChange={(event) => setForm((current) => ({ ...current, currency: event.target.value }))} fullWidth />
            <TextField label="Currency Symbol" value={form.currency_symbol} onChange={(event) => setForm((current) => ({ ...current, currency_symbol: event.target.value }))} fullWidth />
            <TextField select label="First Day of Week" value={form.first_day_of_week} onChange={(event) => setForm((current) => ({ ...current, first_day_of_week: event.target.value }))} fullWidth>
              {['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].map((value) => (
                <MenuItem key={value} value={value}>{value}</MenuItem>
              ))}
            </TextField>
          </Stack>

          <Button variant="contained" disabled={saving || loading} onClick={() => dispatch(updateLocalization(form))}>
            Save Localization
          </Button>
        </Stack>
      </Paper>
    </SettingsPageShell>
  );
}
