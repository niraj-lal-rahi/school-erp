import AdminPanelSettingsOutlinedIcon from '@mui/icons-material/AdminPanelSettingsOutlined';
import LockOutlinedIcon from '@mui/icons-material/LockOutlined';
import {
  Alert,
  Avatar,
  Box,
  Button,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useEffect, useState } from 'react';
import { Link as RouterLink, useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { platformLogin } from './authSlice';
import { persistSession } from '../../utils/tokenStorage';
import { flattenPermissions } from './authUtils';
import { getDefaultAppRoute } from '../portal/utils/getDefaultAppRoute';

export function PlatformLoginPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { loading, error, accessToken, user } = useAppSelector((state) => state.auth);
  const [form, setForm] = useState({
    email: '',
    password: '',
  });

  useEffect(() => {
    if (accessToken) {
      navigate(getDefaultAppRoute(user), { replace: true });
    }
  }, [accessToken, navigate, user]);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await dispatch(platformLogin(form));

    if (!result.error) {
      const permissions = flattenPermissions(result.payload.user);
      const route = getDefaultAppRoute({
        ...result.payload.user,
        permissions,
      });

      persistSession({
        accessToken: result.payload.access_token,
        refreshToken: result.payload.refresh_token,
        tenantCode: null,
      });

      navigate(route, { replace: true });
    }
  }

  return (
    <Box
      sx={{
        minHeight: '100vh',
        display: 'grid',
        placeItems: 'center',
        px: 2,
        background:
          'radial-gradient(circle at top, rgba(20,33,61,0.18), transparent 24%), linear-gradient(180deg, #f7f9fc 0%, #edf3fb 100%)',
      }}
    >
      <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 4, width: '100%', maxWidth: 420, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={3}>
          <Stack alignItems="center" spacing={1}>
            <Avatar sx={{ bgcolor: 'primary.main', width: 56, height: 56 }}>
              <AdminPanelSettingsOutlinedIcon />
            </Avatar>
            <Typography variant="h5">Platform Admin Login</Typography>
            <Typography variant="body2" color="text.secondary" textAlign="center">
              Use this separate control-plane login for super admin and platform administrator access. Tenant code is not required here.
            </Typography>
          </Stack>

          {error ? <Alert severity="error">{error}</Alert> : null}

          <TextField label="Email" type="email" value={form.email} onChange={(e) => setForm((current) => ({ ...current, email: e.target.value }))} required />
          <TextField label="Password" type="password" value={form.password} onChange={(e) => setForm((current) => ({ ...current, password: e.target.value }))} required />

          <Button type="submit" variant="contained" startIcon={<LockOutlinedIcon />} disabled={loading}>
            {loading ? 'Signing in...' : 'Sign In to Platform'}
          </Button>

          <Button component={RouterLink} to="/login" variant="text">
            Back to tenant login
          </Button>
        </Stack>
      </Paper>
    </Box>
  );
}
