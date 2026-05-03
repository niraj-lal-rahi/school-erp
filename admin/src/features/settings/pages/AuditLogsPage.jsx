import HistoryOutlinedIcon from '@mui/icons-material/HistoryOutlined';
import { Alert, Paper, Stack, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { SettingsPageShell } from '../components/SettingsPageShell';
import { fetchAuditLogs } from '../store/settingsSlice';

export function AuditLogsPage() {
  const dispatch = useAppDispatch();
  const { auditLogs, loading, error } = useAppSelector((state) => state.settings);
  const [search, setSearch] = useState('');

  useEffect(() => {
    dispatch(fetchAuditLogs());
  }, [dispatch]);

  const rows = auditLogs.filter((item) => {
    if (!search) {
      return true;
    }

    return `${item.setting_type} ${item.setting_key || ''} ${item.changed_by || ''}`.toLowerCase().includes(search.toLowerCase());
  });

  return (
    <SettingsPageShell
      title="Settings Audit Logs"
      description="Review who changed what, when, and from where so configuration changes stay traceable and easier to debug during live operations."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction="row" spacing={1.5} alignItems="center">
          <HistoryOutlinedIcon color="primary" />
          <Typography variant="h6">Configuration Change Timeline</Typography>
        </Stack>
        <Typography variant="body2" color="text.secondary" mt={1.5}>
          Sensitive changes remain masked in the backend audit trail, but the timeline still tells us exactly which setting family moved.
        </Typography>
      </Paper>

      <AppDataTable
        title="Audit Trail"
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={null}
        filters={[]}
        columns={[
          { key: 'setting_type', header: 'Type' },
          { key: 'setting_key', header: 'Key' },
          { key: 'old_value', header: 'Old Value' },
          { key: 'new_value', header: 'New Value' },
          { key: 'ip_address', header: 'IP Address' },
          { key: 'created_at', header: 'Changed At' },
        ]}
      />
    </SettingsPageShell>
  );
}
