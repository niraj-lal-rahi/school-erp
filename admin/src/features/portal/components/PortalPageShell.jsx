import DashboardOutlinedIcon from '@mui/icons-material/DashboardOutlined';
import DescriptionOutlinedIcon from '@mui/icons-material/DescriptionOutlined';
import EventAvailableOutlinedIcon from '@mui/icons-material/EventAvailableOutlined';
import ForumOutlinedIcon from '@mui/icons-material/ForumOutlined';
import InsertDriveFileOutlinedIcon from '@mui/icons-material/InsertDriveFileOutlined';
import PaymentsOutlinedIcon from '@mui/icons-material/PaymentsOutlined';
import RouteOutlinedIcon from '@mui/icons-material/RouteOutlined';
import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import SettingsOutlinedIcon from '@mui/icons-material/SettingsOutlined';
import TodayOutlinedIcon from '@mui/icons-material/TodayOutlined';
import ViewWeekOutlinedIcon from '@mui/icons-material/ViewWeekOutlined';
import {
  Box,
  Chip,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import { NavLink } from 'react-router-dom';
import { PortalContextSwitcher } from './PortalContextSwitcher';

const portalLinks = [
  { label: 'Dashboard', to: '/portal/dashboard', icon: <DashboardOutlinedIcon fontSize="small" /> },
  { label: 'Overview', to: '/portal/overview', icon: <SchoolOutlinedIcon fontSize="small" /> },
  { label: 'Attendance', to: '/portal/attendance', icon: <EventAvailableOutlinedIcon fontSize="small" /> },
  { label: 'Fees', to: '/portal/fees', icon: <PaymentsOutlinedIcon fontSize="small" /> },
  { label: 'Results', to: '/portal/results', icon: <DescriptionOutlinedIcon fontSize="small" /> },
  { label: 'Timetable', to: '/portal/timetable', icon: <ViewWeekOutlinedIcon fontSize="small" /> },
  { label: 'Assignments', to: '/portal/assignments', icon: <TodayOutlinedIcon fontSize="small" /> },
  { label: 'Transport', to: '/portal/transport', icon: <RouteOutlinedIcon fontSize="small" /> },
  { label: 'Documents', to: '/portal/documents', icon: <InsertDriveFileOutlinedIcon fontSize="small" /> },
  { label: 'Updates', to: '/portal/messages', icon: <ForumOutlinedIcon fontSize="small" /> },
  { label: 'Notifications', to: '/portal/notifications', icon: <ForumOutlinedIcon fontSize="small" /> },
  { label: 'Settings', to: '/portal/settings', icon: <SettingsOutlinedIcon fontSize="small" /> },
];

export function PortalPageShell({
  title,
  description,
  modeLabel,
  actions = null,
  children,
}) {
  return (
    <Stack spacing={3}>
      <Paper
        elevation={0}
        sx={{
          p: { xs: 2.5, md: 3.5 },
          border: '1px solid rgba(20,33,61,0.08)',
          background: 'linear-gradient(135deg, rgba(18,97,78,0.10) 0%, rgba(255,246,229,0.85) 100%)',
        }}
      >
        <Stack spacing={2.5}>
          <Stack
            direction={{ xs: 'column', lg: 'row' }}
            justifyContent="space-between"
            spacing={2}
            alignItems={{ xs: 'flex-start', lg: 'center' }}
          >
            <Stack spacing={1}>
              <Stack direction="row" spacing={1} alignItems="center" flexWrap="wrap">
                <Typography variant="h4">{title}</Typography>
                {modeLabel ? <Chip size="small" color="primary" label={modeLabel} /> : null}
              </Stack>
              <Typography variant="body1" color="text.secondary" sx={{ maxWidth: 760 }}>
                {description}
              </Typography>
            </Stack>
            {actions}
          </Stack>

          <PortalContextSwitcher />

          <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
            {portalLinks.map((link) => (
              <Chip
                key={link.to}
                component={NavLink}
                clickable
                icon={link.icon}
                label={link.label}
                to={link.to}
                variant="outlined"
                sx={{
                  borderRadius: 2.5,
                  '&.active': {
                    backgroundColor: 'rgba(11, 110, 79, 0.12)',
                    borderColor: 'primary.main',
                    color: 'primary.main',
                  },
                }}
              />
            ))}
          </Box>
        </Stack>
      </Paper>

      {children}
    </Stack>
  );
}
