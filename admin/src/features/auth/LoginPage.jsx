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
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { login } from './authSlice';

export function LoginPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const { loading, error, accessToken } = useAppSelector((state) => state.auth);
  const [form, setForm] = useState({
    tenant_code: localStorage.getItem('tenant_code') || 'greenwood',
    email: 'admin@greenwood.edu',
    password: 'password123',
  });

  useEffect(() => {
    if (accessToken) {
      navigate('/students', { replace: true });
    }
  }, [accessToken, navigate]);

  async function handleSubmit(event) {
    event.preventDefault();
    const result = await dispatch(login(form));

    if (!result.error) {
      localStorage.setItem('access_token', result.payload.access_token);
      localStorage.setItem('refresh_token', result.payload.refresh_token);
      localStorage.setItem('tenant_code', result.payload.tenant.code);
      navigate('/students', { replace: true });
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
          'radial-gradient(circle at top, rgba(11,110,79,0.18), transparent 24%), linear-gradient(180deg, #f8faf7 0%, #edf2f8 100%)',
      }}
    >
      <Paper component="form" onSubmit={handleSubmit} elevation={0} sx={{ p: 4, width: '100%', maxWidth: 420, border: '1px solid rgba(20,33,61,0.08)' }}>
        <Stack spacing={3}>
          <Stack alignItems="center" spacing={1}>
            <Avatar sx={{ bgcolor: 'primary.main', width: 56, height: 56 }}>
              <LockOutlinedIcon />
            </Avatar>
            <Typography variant="h5">Admin Login</Typography>
            <Typography variant="body2" color="text.secondary" textAlign="center">
              Sign in to manage students, guardians, classes, academic years, and document records.
            </Typography>
          </Stack>

          {error ? <Alert severity="error">{error}</Alert> : null}

          <TextField label="Tenant Code" value={form.tenant_code} onChange={(e) => setForm((current) => ({ ...current, tenant_code: e.target.value }))} required />
          <TextField label="Email" type="email" value={form.email} onChange={(e) => setForm((current) => ({ ...current, email: e.target.value }))} required />
          <TextField label="Password" type="password" value={form.password} onChange={(e) => setForm((current) => ({ ...current, password: e.target.value }))} required />

          <Button type="submit" variant="contained" disabled={loading}>
            {loading ? 'Signing in...' : 'Sign In'}
          </Button>
        </Stack>
      </Paper>
    </Box>
  );
}
