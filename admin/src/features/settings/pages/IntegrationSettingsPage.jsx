import CloudOutlinedIcon from '@mui/icons-material/CloudOutlined';
import DeleteOutlineOutlinedIcon from '@mui/icons-material/DeleteOutlineOutlined';
import { Alert, Button, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SettingsPageShell } from '../components/SettingsPageShell';
import { createIntegration, deleteIntegration, fetchIntegrations, updateIntegration } from '../store/settingsSlice';

const initialForm = {
  integration_type: 'email',
  provider: '',
  status: 'active',
  configText: '{\n  "sender_name": ""\n}',
  secretText: '{\n  "api_key": ""\n}',
};

function parseJson(text) {
  try {
    return text ? JSON.parse(text) : {};
  } catch {
    return {};
  }
}

export function IntegrationSettingsPage() {
  const dispatch = useAppDispatch();
  const { integrations, loading, saving, error } = useAppSelector((state) => state.settings);
  const [search, setSearch] = useState('');
  const [form, setForm] = useState(initialForm);

  useEffect(() => {
    dispatch(fetchIntegrations());
  }, [dispatch]);

  const rows = integrations.filter((item) => {
    if (!search) {
      return true;
    }

    return `${item.integration_type} ${item.provider || ''}`.toLowerCase().includes(search.toLowerCase());
  });

  const payload = {
    integration_type: form.integration_type,
    provider: form.provider,
    status: form.status,
    config: parseJson(form.configText),
    encrypted_config: parseJson(form.secretText),
  };

  return (
    <SettingsPageShell
      title="Integration Settings"
      description="Manage operational integrations like email, SMS, payment, and storage providers without ever echoing back protected secret material."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={2.5}>
          <Stack direction="row" spacing={1.5} alignItems="center">
            <CloudOutlinedIcon color="primary" />
            <Typography variant="h6">Integration Connection Form</Typography>
          </Stack>

          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
            <TextField
              select
              label="Integration Type"
              value={form.integration_type}
              onChange={(event) => setForm((current) => ({ ...current, integration_type: event.target.value }))}
              fullWidth
            >
              {['email', 'sms', 'payment', 'storage', 'push', 'maps', 'other'].map((value) => (
                <MenuItem key={value} value={value}>{value}</MenuItem>
              ))}
            </TextField>
            <TextField label="Provider" value={form.provider} onChange={(event) => setForm((current) => ({ ...current, provider: event.target.value }))} fullWidth />
            <TextField
              select
              label="Status"
              value={form.status}
              onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}
              fullWidth
            >
              {['active', 'inactive'].map((value) => (
                <MenuItem key={value} value={value}>{value}</MenuItem>
              ))}
            </TextField>
          </Stack>

          <TextField label="Safe Config (JSON)" value={form.configText} onChange={(event) => setForm((current) => ({ ...current, configText: event.target.value }))} multiline minRows={5} fullWidth />
          <TextField label="Secret Config (JSON)" value={form.secretText} onChange={(event) => setForm((current) => ({ ...current, secretText: event.target.value }))} multiline minRows={5} fullWidth helperText="Secret config is submitted, but the API never echoes it back." />

          <Button
            variant="contained"
            disabled={saving}
            onClick={() => dispatch(createIntegration(payload)).then((result) => {
              if (!result.error) {
                setForm(initialForm);
              }
            })}
          >
            Create Integration
          </Button>
        </Stack>
      </Paper>

      <AppDataTable
        title="Integration Registry"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={null}
        filters={[]}
        columns={[
          { key: 'integration_type', header: 'Type' },
          { key: 'provider', header: 'Provider' },
          { key: 'status', header: 'Status' },
          {
            key: 'config',
            header: 'Safe Config',
            render: (row) => (
              <pre style={{ margin: 0, whiteSpace: 'pre-wrap', fontSize: 12 }}>
                {JSON.stringify(row.config || {}, null, 2)}
              </pre>
            ),
          },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Button
                  size="small"
                  onClick={() => dispatch(updateIntegration({
                    id: row.id,
                    payload: { status: row.status === 'active' ? 'inactive' : 'active' },
                  }))}
                >
                  Toggle Status
                </Button>
                <Button
                  size="small"
                  color="error"
                  startIcon={<DeleteOutlineOutlinedIcon />}
                  onClick={() => dispatch(deleteIntegration(row.id))}
                >
                  Delete
                </Button>
              </Stack>
            ),
          },
        ]}
      />
    </SettingsPageShell>
  );
}
