import StorageOutlinedIcon from '@mui/icons-material/StorageOutlined';
import { Button, Paper, Stack, Typography } from '@mui/material';
import { NavLink } from 'react-router-dom';

const navItems = [
  { label: 'Tenant List', to: '/platform/tenants' },
  { label: 'Audit Logs', to: '/platform/audit-logs' },
  { label: 'Health Check', to: '/platform/health' },
];

export function PlatformPageShell({ title, description, actions, children, extraNavItems = [] }) {
  const items = [...navItems, ...extraNavItems];

  return (
    <Stack spacing={3}>
      <Paper
        elevation={0}
        sx={{
          p: 3,
          border: '1px solid rgba(20,33,61,0.08)',
          background: 'linear-gradient(135deg, #ffffff 0%, #eef4fb 46%, #edf8f1 100%)',
        }}
      >
        <Stack spacing={2.5}>
          <Stack direction={{ xs: 'column', lg: 'row' }} justifyContent="space-between" spacing={2}>
            <Stack spacing={1}>
              <Stack direction="row" spacing={1.5} alignItems="center">
                <StorageOutlinedIcon color="primary" />
                <Typography variant="h5">{title}</Typography>
              </Stack>
              <Typography variant="body2" color="text.secondary">
                {description}
              </Typography>
            </Stack>
            {actions}
          </Stack>

          <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap">
            {items.map((item) => (
              <Button
                key={item.to}
                component={NavLink}
                to={item.to}
                size="small"
                variant="outlined"
                sx={{
                  borderRadius: 999,
                  textTransform: 'none',
                  '&.active': {
                    bgcolor: 'primary.main',
                    color: 'primary.contrastText',
                    borderColor: 'primary.main',
                  },
                }}
              >
                {item.label}
              </Button>
            ))}
          </Stack>
        </Stack>
      </Paper>

      {children}
    </Stack>
  );
}
