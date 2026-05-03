import AddOutlinedIcon from '@mui/icons-material/AddOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Chip, MenuItem, Paper, Stack, Switch, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { useSettingsAccess } from '../hooks/useSettingsAccess';
import { createSetting, fetchSettingGroups, fetchSettings, updateSetting } from '../store/settingsSlice';

const initialForm = {
  key: '',
  value: '',
  value_type: 'string',
  scope: 'tenant',
  is_public: false,
  is_sensitive: false,
  description: '',
};

function stringifyValue(value) {
  if (value === null || value === undefined) {
    return '';
  }

  if (typeof value === 'object') {
    return JSON.stringify(value);
  }

  return String(value);
}

function normalizeValue(rawValue, valueType) {
  if (valueType === 'integer') {
    return Number(rawValue || 0);
  }

  if (valueType === 'boolean') {
    return rawValue === true || rawValue === 'true' || rawValue === '1';
  }

  if (valueType === 'json') {
    try {
      return rawValue ? JSON.parse(rawValue) : {};
    } catch {
      return {};
    }
  }

  return rawValue;
}

export function SettingsGroupEditor({ groupCode, title, description, suggestedPrefix }) {
  const dispatch = useAppDispatch();
  const { canManage, canManageGlobal } = useSettingsAccess();
  const { groups, settings, loading, saving, error } = useAppSelector((state) => state.settings);
  const [search, setSearch] = useState('');
  const [scope, setScope] = useState('tenant');
  const [form, setForm] = useState(initialForm);
  const [draftValues, setDraftValues] = useState({});

  useEffect(() => {
    dispatch(fetchSettingGroups({ scope }));
    dispatch(fetchSettings({ scope }));
  }, [dispatch, scope]);

  const matchingGroup = useMemo(
    () => groups.find((group) => group.code === groupCode),
    [groupCode, groups],
  );

  const rows = useMemo(() => {
    const baseRows = matchingGroup ? settings.filter((setting) => setting.group_id === matchingGroup.id) : [];

    return baseRows.filter((setting) => {
      if (!search) {
        return true;
      }

      return `${setting.key} ${setting.description || ''}`.toLowerCase().includes(search.toLowerCase());
    });
  }, [matchingGroup, search, settings]);

  const createPayload = {
    ...form,
    key: form.key.includes('.') ? form.key : `${suggestedPrefix || groupCode}.${form.key}`,
    value: normalizeValue(form.value, form.value_type),
    group_id: matchingGroup?.id,
  };

  return (
    <Stack spacing={3}>
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={1.5}>
          <Typography variant="h6">{title}</Typography>
          <Typography variant="body2" color="text.secondary">
            {description}
          </Typography>
        </Stack>
      </Paper>

      {canManage ? (
        <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
          <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} useFlexGap flexWrap="wrap" alignItems="center">
            <TextField
              label="Setting Key"
              value={form.key}
              onChange={(event) => setForm((current) => ({ ...current, key: event.target.value }))}
              helperText={`Prefix suggestion: ${suggestedPrefix || groupCode}.`}
              sx={{ minWidth: 240 }}
            />
            <TextField
              label="Value"
              value={form.value}
              onChange={(event) => setForm((current) => ({ ...current, value: event.target.value }))}
              sx={{ minWidth: 220, flex: 1 }}
            />
            <TextField
              select
              label="Type"
              value={form.value_type}
              onChange={(event) => setForm((current) => ({ ...current, value_type: event.target.value }))}
              sx={{ minWidth: 150 }}
            >
              {['string', 'integer', 'boolean', 'json', 'encrypted'].map((value) => (
                <MenuItem key={value} value={value}>{value}</MenuItem>
              ))}
            </TextField>
            {canManageGlobal ? (
              <TextField
                select
                label="Scope"
                value={form.scope}
                onChange={(event) => setForm((current) => ({ ...current, scope: event.target.value }))}
                sx={{ minWidth: 150 }}
              >
                <MenuItem value="tenant">tenant</MenuItem>
                <MenuItem value="global">global</MenuItem>
              </TextField>
            ) : null}
            <TextField
              label="Description"
              value={form.description}
              onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))}
              sx={{ minWidth: 240, flex: 1 }}
            />
            <Stack direction="row" spacing={1} alignItems="center">
              <Typography variant="caption">Public</Typography>
              <Switch checked={form.is_public} onChange={(event) => setForm((current) => ({ ...current, is_public: event.target.checked }))} />
            </Stack>
            <Stack direction="row" spacing={1} alignItems="center">
              <Typography variant="caption">Sensitive</Typography>
              <Switch checked={form.is_sensitive} onChange={(event) => setForm((current) => ({ ...current, is_sensitive: event.target.checked }))} />
            </Stack>
            <Button
              variant="contained"
              startIcon={<AddOutlinedIcon />}
              disabled={saving || !form.key || !matchingGroup}
              onClick={() => dispatch(createSetting(createPayload)).then((result) => {
                if (!result.error) {
                  setForm(initialForm);
                  dispatch(fetchSettings({ scope }));
                }
              })}
            >
              Add Setting
            </Button>
          </Stack>
        </Paper>
      ) : null}

      <AppDataTable
        title={`${title} Records`}
        rows={rows}
        loading={loading}
        searchValue={search}
        onSearchChange={setSearch}
        pagination={null}
        filters={[
          ...(canManageGlobal ? [{
            key: 'scope',
            label: 'Scope',
            value: scope,
            onChange: setScope,
            options: [
              { label: 'Tenant', value: 'tenant' },
              { label: 'Global', value: 'global' },
            ],
          }] : []),
        ]}
        columns={[
          { key: 'key', header: 'Key' },
          {
            key: 'value',
            header: 'Value',
            render: (row) => (
              <TextField
                size="small"
                fullWidth
                value={draftValues[row.id] ?? stringifyValue(row.value)}
                onChange={(event) => setDraftValues((current) => ({ ...current, [row.id]: event.target.value }))}
                placeholder={row.is_sensitive ? 'Masked sensitive value' : ''}
              />
            ),
          },
          { key: 'value_type', header: 'Type' },
          { key: 'scope', header: 'Scope' },
          {
            key: 'visibility',
            header: 'Visibility',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                <Chip size="small" label={row.is_public ? 'Public' : 'Private'} color={row.is_public ? 'success' : 'default'} />
                {row.is_sensitive ? <Chip size="small" label="Sensitive" color="warning" /> : null}
              </Stack>
            ),
          },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => (
              <Stack direction="row" spacing={1}>
                {canManage ? (
                  <Button
                    size="small"
                    startIcon={<SaveOutlinedIcon />}
                    onClick={() => dispatch(updateSetting({
                      id: row.id,
                      payload: {
                        value: normalizeValue(draftValues[row.id] ?? stringifyValue(row.value), row.value_type),
                      },
                    }))}
                  >
                    Save
                  </Button>
                ) : null}
                {canManage ? (
                  <Button
                    size="small"
                    onClick={() => dispatch(updateSetting({
                      id: row.id,
                      payload: {
                        is_public: !row.is_public,
                      },
                    }))}
                  >
                    {row.is_public ? 'Make Private' : 'Make Public'}
                  </Button>
                ) : null}
              </Stack>
            ),
          },
        ]}
      />
    </Stack>
  );
}
