import SecurityOutlinedIcon from '@mui/icons-material/SecurityOutlined';
import { Alert, Button, Chip, Paper, Stack, TextField, Typography } from '@mui/material';
import { useEffect, useState } from 'react';
import { useAppDispatch, useAppSelector } from '../../../hooks/redux';
import { RbacPageShell } from '../components/RbacPageShell';
import { checkPermission, clearPermissionCheck, fetchMyPermissions, fetchMyRoles } from '../store/rbacSlice';

export function MyPermissionsPage() {
  const dispatch = useAppDispatch();
  const { myPermissions, myRoles, lastPermissionCheck, loading, saving, error } = useAppSelector((state) => state.rbac);
  const [permissionCode, setPermissionCode] = useState('');

  useEffect(() => {
    dispatch(fetchMyPermissions());
    dispatch(fetchMyRoles());
    dispatch(clearPermissionCheck());
  }, [dispatch]);

  return (
    <RbacPageShell
      title="My Permissions"
      description="See exactly what the current account can do, which roles are driving that access, and verify individual permission checks before troubleshooting UI visibility."
    >
      {error ? <Alert severity="error">{error}</Alert> : null}

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ xs: 'stretch', md: 'flex-end' }}>
          <TextField
            fullWidth
            label="Permission Code"
            value={permissionCode}
            onChange={(event) => setPermissionCode(event.target.value)}
            placeholder="finance.manage"
          />
          <Button
            variant="contained"
            startIcon={<SecurityOutlinedIcon />}
            disabled={!permissionCode || saving}
            onClick={() => dispatch(checkPermission({ permission_code: permissionCode }))}
          >
            {saving ? 'Checking...' : 'Check Permission'}
          </Button>
        </Stack>

        {lastPermissionCheck ? (
          <Alert severity={lastPermissionCheck.allowed ? 'success' : 'warning'} sx={{ mt: 2 }}>
            {lastPermissionCheck.permission_code} is {lastPermissionCheck.allowed ? 'allowed' : 'not allowed'} for the current user.
          </Alert>
        ) : null}
      </Paper>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Typography variant="h6" gutterBottom>My Roles</Typography>
        <Stack direction="row" flexWrap="wrap" gap={1.5}>
          {loading ? 'Loading roles...' : myRoles.length ? myRoles.map((role) => (
            <Chip key={role.id} label={`${role.name} (${role.code})`} color="primary" variant="outlined" />
          )) : <Typography color="text.secondary">No roles resolved for the current session.</Typography>}
        </Stack>
      </Paper>

      <Paper elevation={0} sx={{ p: 3, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Typography variant="h6" gutterBottom>My Permission Codes</Typography>
        <Stack direction="row" flexWrap="wrap" gap={1}>
          {loading ? 'Loading permissions...' : myPermissions.length ? myPermissions.map((permission) => (
            <Chip key={permission.id} label={permission.code} variant="outlined" />
          )) : <Typography color="text.secondary">No permissions resolved for the current session.</Typography>}
        </Stack>
      </Paper>
    </RbacPageShell>
  );
}
