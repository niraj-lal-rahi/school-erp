import PersonAddAltOutlinedIcon from '@mui/icons-material/PersonAddAltOutlined';
import RemoveCircleOutlineOutlinedIcon from '@mui/icons-material/RemoveCircleOutlineOutlined';
import { Alert, Button, MenuItem, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { AppDataTable } from '../../../components/common/AppDataTable';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { RbacPageShell } from '../components/RbacPageShell';
import { useRbacAccess } from '../hooks/useRbacAccess';
import { assignUserRole, fetchRoles, fetchUserRoles, removeUserRole, setSelectedUserId } from '../store/rbacSlice';

export function AssignRolesPage() {
  const dispatch = useAppDispatch();
  const { canManage } = useRbacAccess();
  const { roles, selectedUserRoles, selectedUserId, loading, saving, error } = useAppSelector((state) => state.rbac);
  const [userIdInput, setUserIdInput] = useState(selectedUserId || '');
  const [roleId, setRoleId] = useState('');

  useEffect(() => {
    dispatch(fetchRoles({ per_page: 200, status: 'active' }));
  }, [dispatch]);

  const availableRoles = useMemo(
    () => roles.filter((role) => !selectedUserRoles.some((assignment) => assignment.role_id === role.id)),
    [roles, selectedUserRoles],
  );

  function loadAssignments() {
    if (!userIdInput) {
      return;
    }

    dispatch(setSelectedUserId(userIdInput));
    dispatch(fetchUserRoles(userIdInput));
  }

  return (
    <RbacPageShell
      title="Assign Roles to Users"
      description="Load a user by ID, review the current assignments, and add or remove tenant-safe roles without leaving the RBAC workspace."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2}>
          <TextField
            fullWidth
            label="Target User ID"
            value={userIdInput}
            onChange={(event) => setUserIdInput(event.target.value.replace(/[^\d]/g, ''))}
            helperText="The current backend exposes assignment by user ID, so this screen uses an explicit numeric target."
          />
          <Button variant="outlined" onClick={loadAssignments} disabled={!userIdInput || loading}>
            Load Roles
          </Button>
        </Stack>
      </Paper>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ xs: 'stretch', md: 'flex-end' }}>
          <TextField
            select
            fullWidth
            label="Role to Assign"
            value={roleId}
            onChange={(event) => setRoleId(event.target.value)}
          >
            {availableRoles.map((role) => (
              <MenuItem key={role.id} value={role.id}>
                {role.name} ({role.code})
              </MenuItem>
            ))}
          </TextField>
          <Button
            variant="contained"
            startIcon={<PersonAddAltOutlinedIcon />}
            disabled={!canManage || !selectedUserId || !roleId || saving}
            onClick={() => dispatch(assignUserRole({
              userId: selectedUserId,
              payload: { role_id: Number(roleId) },
            }))}
          >
            {saving ? 'Assigning...' : 'Assign Role'}
          </Button>
        </Stack>
      </Paper>

      <AppDataTable
        title="Current User Roles"
        columns={[
          { key: 'id', header: 'Assignment ID' },
          { key: 'user_id', header: 'User ID' },
          { key: 'role_name', header: 'Role', render: (row) => row.role?.name || 'Unknown role' },
          { key: 'role_code', header: 'Code', render: (row) => row.role?.code || 'Unknown code' },
          { key: 'role_type', header: 'Type', render: (row) => row.role?.role_type || 'tenant' },
          {
            key: 'actions',
            header: 'Actions',
            render: (row) => canManage ? (
              <Button
                size="small"
                color="error"
                startIcon={<RemoveCircleOutlineOutlinedIcon />}
                onClick={() => dispatch(removeUserRole({ userId: selectedUserId, roleId: row.role_id }))}
              >
                Remove
              </Button>
            ) : 'View only',
          },
        ]}
        rows={selectedUserRoles}
        loading={loading}
        searchValue=""
        onSearchChange={() => {}}
        pagination={null}
        emptyState={selectedUserId ? 'No roles assigned to this user.' : 'Load a user to inspect role assignments.'}
      />
    </RbacPageShell>
  );
}
