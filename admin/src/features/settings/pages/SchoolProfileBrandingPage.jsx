import CloudUploadOutlinedIcon from '@mui/icons-material/CloudUploadOutlined';
import PaletteOutlinedIcon from '@mui/icons-material/PaletteOutlined';
import { Alert, Button, Grid, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SettingsPageShell } from '../components/SettingsPageShell';
import { fetchBranding, fetchPublicConfig, updateBranding } from '../store/settingsSlice';

const initialForm = {
  school_name: '',
  primary_color: '#0F4C81',
  secondary_color: '#1C7C54',
  accent_color: '#F4B400',
  footer_text: '',
  custom_css: '',
  logo: null,
  favicon: null,
};

export function SchoolProfileBrandingPage() {
  const dispatch = useAppDispatch();
  const { branding, publicConfig, loading, saving, error } = useAppSelector((state) => state.settings);
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchBranding());
    dispatch(fetchPublicConfig());
  }, [dispatch]);

  useEffect(() => {
    if (branding) {
      setForm((current) => ({
        ...current,
        school_name: branding.school_name || '',
        primary_color: branding.primary_color || '#0F4C81',
        secondary_color: branding.secondary_color || '#1C7C54',
        accent_color: branding.accent_color || '#F4B400',
        footer_text: branding.footer_text || '',
        custom_css: branding.custom_css || '',
      }));
    }
  }, [branding]);

  const submit = () => {
    const payload = new FormData();
    payload.append('school_name', form.school_name);
    payload.append('primary_color', form.primary_color);
    payload.append('secondary_color', form.secondary_color);
    payload.append('accent_color', form.accent_color);
    payload.append('footer_text', form.footer_text);
    payload.append('custom_css', form.custom_css);

    if (form.logo) {
      payload.append('logo', form.logo);
    }

    if (form.favicon) {
      payload.append('favicon', form.favicon);
    }

    dispatch(updateBranding(payload)).then((result) => {
      if (!result.error) {
        dispatch(fetchPublicConfig());
      }
    });
  };

  return (
    <SettingsPageShell
      title="School Profile & Branding"
      description="Shape the tenant’s visual identity with name, color system, upload-ready brand assets, and public-facing footer copy."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, lg: 8 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2.5}>
              <Stack direction="row" spacing={1.5} alignItems="center">
                <PaletteOutlinedIcon color="primary" />
                <Typography variant="h6">Branding Form</Typography>
              </Stack>

              <TextField label="School Name" value={form.school_name} onChange={(event) => setForm((current) => ({ ...current, school_name: event.target.value }))} fullWidth />
              <TextField label="Footer Text" value={form.footer_text} onChange={(event) => setForm((current) => ({ ...current, footer_text: event.target.value }))} fullWidth />
              <TextField label="Custom CSS" value={form.custom_css} onChange={(event) => setForm((current) => ({ ...current, custom_css: event.target.value }))} multiline minRows={5} fullWidth />

              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                {[
                  ['Primary Color', 'primary_color'],
                  ['Secondary Color', 'secondary_color'],
                  ['Accent Color', 'accent_color'],
                ].map(([label, key]) => (
                  <Stack key={key} spacing={1} sx={{ minWidth: 180 }}>
                    <Typography variant="caption" color="text.secondary">{label}</Typography>
                    <TextField
                      type="color"
                      value={form[key]}
                      onChange={(event) => setForm((current) => ({ ...current, [key]: event.target.value }))}
                      sx={{ width: 120 }}
                    />
                  </Stack>
                ))}
              </Stack>

              <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
                <Button component="label" variant="outlined" startIcon={<CloudUploadOutlinedIcon />}>
                  Upload Logo
                  <input hidden type="file" accept="image/*" onChange={(event) => setForm((current) => ({ ...current, logo: event.target.files?.[0] || null }))} />
                </Button>
                <Button component="label" variant="outlined" startIcon={<CloudUploadOutlinedIcon />}>
                  Upload Favicon
                  <input hidden type="file" accept="image/*" onChange={(event) => setForm((current) => ({ ...current, favicon: event.target.files?.[0] || null }))} />
                </Button>
              </Stack>

              <Button variant="contained" disabled={saving || loading} onClick={submit}>
                Save Branding
              </Button>
            </Stack>
          </Paper>
        </Grid>

        <Grid size={{ xs: 12, lg: 4 }}>
          <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
            <Stack spacing={2}>
              <Typography variant="h6">Public Preview Snapshot</Typography>
              <Typography variant="body2" color="text.secondary">
                This is the safe public branding block the frontend can consume without touching any private config.
              </Typography>
              <pre style={{ margin: 0, whiteSpace: 'pre-wrap', fontSize: 12 }}>
                {JSON.stringify(publicConfig?.branding || {}, null, 2)}
              </pre>
            </Stack>
          </Paper>
        </Grid>
      </Grid>
    </SettingsPageShell>
  );
}
