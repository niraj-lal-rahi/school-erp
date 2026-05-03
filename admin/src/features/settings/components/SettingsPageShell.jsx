import SettingsSuggestOutlinedIcon from '@mui/icons-material/SettingsSuggestOutlined';
import { Button, Paper, Stack, Typography } from '@mui/material';
import { NavLink } from 'react-router-dom';

const navItems = [
  { label: 'General', to: '/settings/general' },
  { label: 'Branding', to: '/settings/branding' },
  { label: 'Academic', to: '/settings/academic' },
  { label: 'Finance', to: '/settings/finance' },
  { label: 'Notifications', to: '/settings/notifications' },
  { label: 'Security', to: '/settings/security' },
  { label: 'Localization', to: '/settings/localization' },
  { label: 'Feature Flags', to: '/settings/features' },
  { label: 'Integrations', to: '/settings/integrations' },
  { label: 'Audit Logs', to: '/settings/audit-logs' },
  { label: 'Public Config', to: '/settings/public-config' },
];

export function SettingsPageShell({ title, description, actions, children }) {
  return (
    <Stack spacing={3}>
      <Paper
        elevation={0}
        sx={{
          p: 3,
          border: '1px solid rgba(20,33,61,0.08)',
          background: 'linear-gradient(135deg, #ffffff 0%, #f3f8ef 52%, #eef4fb 100%)',
        }}
      >
        <Stack spacing={2.5}>
          <Stack direction={{ xs: 'column', lg: 'row' }} justifyContent="space-between" spacing={2}>
            <Stack spacing={1}>
              <Stack direction="row" spacing={1.5} alignItems="center">
                <SettingsSuggestOutlinedIcon color="primary" />
                <Typography variant="h5">{title}</Typography>
              </Stack>
              <Typography variant="body2" color="text.secondary">
                {description}
              </Typography>
            </Stack>
            {actions}
          </Stack>

          <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap">
            {navItems.map((item) => (
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
