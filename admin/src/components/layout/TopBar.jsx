import SearchIcon from '@mui/icons-material/Search';
import LogoutOutlinedIcon from '@mui/icons-material/LogoutOutlined';
import {
  Avatar,
  Box,
  Button,
  Chip,
  InputAdornment,
  Paper,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useNavigate } from 'react-router-dom';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { clearSession, logout } from '../../features/auth/authSlice';

export function TopBar() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const user = useAppSelector((state) => state.auth.user);
  const tenantCode = useAppSelector((state) => state.auth.tenantCode);

  async function handleLogout() {
    await dispatch(logout());
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('tenant_code');
    dispatch(clearSession());
    navigate('/login');
  }

  return (
    <Paper
      elevation={0}
      sx={{
        p: 2,
        border: '1px solid rgba(20,33,61,0.08)',
        background: 'linear-gradient(120deg, #ffffff 0%, #fff7e8 100%)',
      }}
    >
      <Stack direction={{ xs: 'column', md: 'row' }} justifyContent="space-between" spacing={2} alignItems={{ md: 'center' }}>
        <Box>
          <Typography variant="h4">Student Module</Typography>
          <Typography variant="body2" color="text.secondary">
            Admissions, lifecycle, guardians, documents, and profile management.
          </Typography>
        </Box>

        <Stack direction={{ xs: 'column', md: 'row' }} spacing={2} alignItems={{ md: 'center' }}>
          <TextField
            size="small"
            placeholder="Quick search"
            InputProps={{
              startAdornment: (
                <InputAdornment position="start">
                  <SearchIcon fontSize="small" />
                </InputAdornment>
              ),
            }}
          />
          <Chip label={`Tenant: ${tenantCode || 'n/a'}`} color="secondary" variant="outlined" />
          <Button variant="outlined" color="inherit" startIcon={<LogoutOutlinedIcon />} onClick={handleLogout}>
            Logout
          </Button>
          <Stack direction="row" spacing={1} alignItems="center">
            <Avatar>{user?.name?.[0] || 'A'}</Avatar>
            <Box>
              <Typography variant="body2" fontWeight={700}>
                {user?.name}
              </Typography>
              <Typography variant="caption" color="text.secondary">
                {user?.role}
              </Typography>
            </Box>
          </Stack>
        </Stack>
      </Stack>
    </Paper>
  );
}
