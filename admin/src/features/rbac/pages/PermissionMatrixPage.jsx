import SaveOutlinedIcon from '@mui/icons-material/SaveOutlined';
import { Alert, Button, Chip, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { RbacPageShell } from '../components/RbacPageShell';
import { useRbacAccess } from '../hooks/useRbacAccess';
import { fetchGroupedPermissions, fetchRoles, syncRolePermissions } from '../store/rbacSlice';

export function PermissionMatrixPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useRbacAccess();
  const { roles, groupedPermissions, loading, saving, error } = useAppSelector((state) => state.rbac);
  const [selectedRoleId, setSelectedRoleId] = useState('');
  const [selectedPermissions, setSelectedPermissions] = useState([]);

  useEffect(() => {
    dispatch(fetchRoles({ per_page: 200, status: 'active' }));
    dispatch(fetchGroupedPermissions());
  }, [dispatch]);

  const selectedRole = useMemo(
    () => roles.find((role) => String(role.id) === String(selectedRoleId)) || null,
    [roles, selectedRoleId],
  );

  useEffect(() => {
    if (selectedRole?.permissions?.length) {
      setSelectedPermissions(selectedRole.permissions.map((permission) => permission.id));
    } else {
      setSelectedPermissions([]);
    }
  }, [selectedRole]);

  function togglePermission(permissionId) {
    setSelectedPermissions((current) => (
      current.includes(permissionId)
        ? current.filter((id) => id !== permissionId)
        : [...current, permissionId]
    ));
  }

  return (
    <RbacPageShell
      title="Permission Matrix"
      description="Review permissions module by module and replace the selected role’s entire permission map in one save action."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}
      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={3}>
          <TextField
            select
            fullWidth
            label="Select Role"
            value={selectedRoleId}
            onChange={(event) => setSelectedRoleId(event.target.value)}
          >
            {roles.map((role) => (
              <MenuItem key={role.id} value={role.id}>
                {role.name} ({role.code})
              </MenuItem>
            ))}
          </TextField>

          {!selectedRoleId ? (
            <Alert severity="info">Choose a role to start editing its permission set.</Alert>
          ) : (
            <Alert severity="warning">
              The current API does not provide a dedicated role detail endpoint, so this screen may not know the existing assignment state until a sync response refreshes it.
            </Alert>
          )}

          {groupedPermissions.map((group) => (
            <Paper key={group.module} variant="outlined" sx={{ p: 2 }}>
              <Stack spacing={1.5}>
                <Typography variant="subtitle1">{group.module}</Typography>
                <Stack direction="row" flexWrap="wrap" gap={1}>
                  {group.permissions.map((permission) => (
                    <Chip
                      key={permission.id}
                      label={permission.code}
                      color={selectedPermissions.includes(permission.id) ? 'primary' : 'default'}
                      variant={selectedPermissions.includes(permission.id) ? 'filled' : 'outlined'}
                      onClick={() => selectedRoleId && canManage ? togglePermission(permission.id) : undefined}
                      clickable={Boolean(selectedRoleId) && canManage}
                    />
                  ))}
                </Stack>
              </Stack>
            </Paper>
          ))}

          <Stack direction="row" justifyContent="flex-end">
            <Button
              variant="contained"
              startIcon={<SaveOutlinedIcon />}
              disabled={!canManage || !selectedRoleId || saving || loading}
              onClick={() => dispatch(syncRolePermissions({
                id: selectedRoleId,
                payload: { permission_ids: selectedPermissions },
              }))}
            >
              {saving ? 'Saving...' : 'Save Permission Matrix'}
            </Button>
          </Stack>
        </Stack>
      </Paper>
    </RbacPageShell>
  );
}
