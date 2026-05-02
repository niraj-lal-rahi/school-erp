import ArrowBackOutlinedIcon from '@mui/icons-material/ArrowBackOutlined';
import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Box, Button, Checkbox, Chip, FormControlLabel, Grid, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { RbacPageShell } from '../components/RbacPageShell';
import { useRbacAccess } from '../hooks/useRbacAccess';
import { createRole, fetchGroupedPermissions, fetchRoles, syncRolePermissions, updateRole } from '../store/rbacSlice';

const roleTypeOptions = [
  { label: 'Tenant', value: 'tenant' },
  { label: 'System', value: 'system' },
];

const statusOptions = [
  { label: 'Active', value: 'active' },
  { label: 'Inactive', value: 'inactive' },
];

const initialForm = {
  name: '',
  code: '',
  description: '',
  role_type: 'tenant',
  is_default: false,
  status: 'active',
};

export function RoleEditorPage() {
  const { roleId } = useParams();
  const isEditing = Boolean(roleId);
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { canManage, isSuperAdmin } = useRbacAccess();
  const { roles, groupedPermissions, loading, saving, error } = useAppSelector((state) => state.rbac);
  const [form, setForm] = useState(initialForm);
  const [selectedPermissions, setSelectedPermissions] = useState([]);
  const currentRole = useMemo(
    () => roles.find((role) => String(role.id) === String(roleId)) || null,
    [roleId, roles],
  );

  useEffect(() => {
    if (!roles.length) {
      dispatch(fetchRoles({ per_page: 200 }));
    }

    dispatch(fetchGroupedPermissions());
  }, [dispatch, roles.length]);

  useEffect(() => {
    if (currentRole) {
      setForm({
        name: currentRole.name || '',
        code: currentRole.code || '',
        description: currentRole.description || '',
        role_type: currentRole.role_type || 'tenant',
        is_default: Boolean(currentRole.is_default),
        status: currentRole.status || 'active',
      });
      setSelectedPermissions((currentRole.permissions || []).map((permission) => permission.id));
    }
  }, [currentRole]);

  function togglePermission(permissionId) {
    setSelectedPermissions((current) => (
      current.includes(permissionId)
        ? current.filter((id) => id !== permissionId)
        : [...current, permissionId]
    ));
  }

  async function handleSubmit(event) {
    event.preventDefault();

    const payload = {
      name: form.name,
      code: form.code.trim(),
      description: form.description || null,
      role_type: form.role_type,
      is_default: Boolean(form.is_default),
      status: form.status,
    };

    let result;

    if (isEditing && currentRole) {
      result = await dispatch(updateRole({ id: currentRole.id, payload }));
    } else {
      result = await dispatch(createRole(payload));
    }

    const role = result.payload;

    if (role?.id && canManage) {
      await dispatch(syncRolePermissions({
        id: role.id,
        payload: {
          permission_ids: selectedPermissions,
        },
      }));
    }

    navigate('/rbac/roles');
  }

  return (
    <RbacPageShell
      title={isEditing ? 'Create / Edit Role' : 'Create / Edit Role'}
      description="Shape the role definition first, then decide exactly which module permissions it should carry. This screen saves the role and its permission map together."
      actions={
        <Button variant="text" startIcon={<ArrowBackOutlinedIcon />} onClick={() => navigate('/rbac/roles')}>
          Back to Roles
        </Button>
      }
    >
      {!canManage ? <Alert severity="warning">You can view role metadata here, but only RBAC managers can save changes.</Alert> : null}
      {error ? <Alert severity="error">{error}</Alert> : null}
      {isEditing && !currentRole && !loading ? (
        <Alert severity="info">This role was not found in the current in-memory list. Reload the roles page first if you came here directly.</Alert>
      ) : null}
      {form.role_type === 'system' && !isSuperAdmin ? (
        <Alert severity="warning">Only a super admin should create or update system roles.</Alert>
      ) : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack component="form" spacing={3} onSubmit={handleSubmit}>
          <Grid container spacing={2}>
            <Grid item xs={12} md={6}>
              <TextField fullWidth label="Role Name" value={form.name} onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))} required />
            </Grid>
            <Grid item xs={12} md={6}>
              <TextField fullWidth label="Role Code" value={form.code} onChange={(event) => setForm((current) => ({ ...current, code: event.target.value.toLowerCase().replace(/\s+/g, '_') }))} required />
            </Grid>
            <Grid item xs={12} md={6}>
              <TextField select fullWidth label="Role Type" value={form.role_type} onChange={(event) => setForm((current) => ({ ...current, role_type: event.target.value }))}>
                {roleTypeOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid item xs={12} md={6}>
              <TextField select fullWidth label="Status" value={form.status} onChange={(event) => setForm((current) => ({ ...current, status: event.target.value }))}>
                {statusOptions.map((option) => (
                  <MenuItem key={option.value} value={option.value}>{option.label}</MenuItem>
                ))}
              </TextField>
            </Grid>
            <Grid item xs={12}>
              <TextField fullWidth multiline minRows={3} label="Description" value={form.description} onChange={(event) => setForm((current) => ({ ...current, description: event.target.value }))} />
            </Grid>
            <Grid item xs={12}>
              <FormControlLabel
                control={<Checkbox checked={form.is_default} onChange={(event) => setForm((current) => ({ ...current, is_default: event.target.checked }))} />}
                label="Mark as default role for this tenant"
              />
            </Grid>
          </Grid>

          <Box>
            <Typography variant="h6" gutterBottom>Permission Snapshot</Typography>
            <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
              This matrix replaces the role’s permission map on save. If the role details came from the list API only, existing checks may start blank until the first sync response refreshes them.
            </Typography>
            <Stack spacing={2}>
              {groupedPermissions.map((group) => (
                <Paper key={group.module} variant="outlined" sx={{ p: 2 }}>
                  <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2}>
                    <Box>
                      <Typography variant="subtitle1">{group.module}</Typography>
                      <Typography variant="body2" color="text.secondary">
                        {group.permissions.length} permissions available
                      </Typography>
                    </Box>
                    <Stack direction="row" flexWrap="wrap" gap={1}>
                      {group.permissions.map((permission) => (
                        <Chip
                          key={permission.id}
                          label={permission.code}
                          color={selectedPermissions.includes(permission.id) ? 'primary' : 'default'}
                          variant={selectedPermissions.includes(permission.id) ? 'filled' : 'outlined'}
                          onClick={() => togglePermission(permission.id)}
                          clickable={canManage}
                        />
                      ))}
                    </Stack>
                  </Stack>
                </Paper>
              ))}
            </Stack>
          </Box>

          <Stack direction="row" justifyContent="flex-end" spacing={1.5}>
            <Button variant="text" onClick={() => navigate('/rbac/roles')}>Cancel</Button>
            <Button type="submit" variant="contained" startIcon={<SaveOutlinedIcon />} disabled={!canManage || saving}>
              {saving ? 'Saving...' : isEditing ? 'Update Role' : 'Create Role'}
            </Button>
          </Stack>
        </Stack>
      </Paper>
    </RbacPageShell>
  );
}
