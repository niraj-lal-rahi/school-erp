import FlagOutlinedIcon from '@mui/icons-material/FlagOutlined';
import { Alert, Button, Chip, Paper, Stack, Switch, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SettingsPageShell } from '../components/SettingsPageShell';
import { disableFeatureFlag, enableFeatureFlag, fetchFeatureFlags } from '../store/settingsSlice';

export function FeatureFlagsPage() {
  const dispatch = useAppDispatch();
  const { features, loading, error } = useAppSelector((state) => state.settings);
  const [search, setSearch] = useState('');
  const [module, setModule] = useState('');

  useEffect(() => {
    dispatch(fetchFeatureFlags({ module }));
  }, [dispatch, module]);

  const rows = features.filter((feature) => {
    if (!search) {
      return true;
    }

    return `${feature.name} ${feature.feature_code} ${feature.module}`.toLowerCase().includes(search.toLowerCase());
  });

  return (
    <SettingsPageShell
      title="Feature Flags"
      description="Roll modules and sub-capabilities out deliberately, with tenant-aware toggles that help us stage access instead of hard-cutting code paths."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction="row" spacing={1.5} alignItems="center">
          <FlagOutlinedIcon color="primary" />
          <Typography variant="h6">Rollout Control</Typography>
        </Stack>
        <Typography variant="body2" color="text.secondary" mt={1.5}>
          Feature flags are useful both for staged tenant rollout and for giving operations a safer control surface than code deploys.
        </Typography>
      </Paper>

      <AppDataTable
        title="Feature Flag Registry"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={null}
        filters={[
          {
            key: 'module',
            label: 'Module',
            value: module,
            onChange: setModule,
            options: [
              { label: 'All Modules', value: '' },
              ...Array.from(new Set(features.map((item) => item.module))).filter(Boolean).map((value) => ({ label: value, value })),
            ],
          },
        ]}
        columns={[
          { key: 'name', header: 'Feature' },
          { key: 'feature_code', header: 'Code' },
          { key: 'module', header: 'Module' },
          { key: 'rollout_percentage', header: 'Rollout %' },
          {
            key: 'status',
            header: 'Status',
            render: (row) => <Chip size="small" label={row.is_enabled ? 'Enabled' : 'Disabled'} color={row.is_enabled ? 'success' : 'default'} />,
          },
          {
            key: 'toggle',
            header: 'Toggle',
            render: (row) => (
              <Switch
                checked={Boolean(row.is_enabled)}
                onChange={(event) => dispatch(event.target.checked ? enableFeatureFlag(row.id) : disableFeatureFlag(row.id))}
              />
            ),
          },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Button size="small" onClick={() => dispatch(row.is_enabled ? disableFeatureFlag(row.id) : enableFeatureFlag(row.id))}>
                {row.is_enabled ? 'Disable' : 'Enable'}
              </Button>
            ),
          },
        ]}
      />
    </SettingsPageShell>
  );
}
