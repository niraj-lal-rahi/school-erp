import CalendarMonthOutlinedIcon from '@mui/icons-material/CalendarMonthOutlined';
import ClassOutlinedIcon from '@mui/icons-material/ClassOutlined';
import PeopleAltOutlinedIcon from '@mui/icons-material/PeopleAltOutlined';
import SupervisedUserCircleOutlinedIcon from '@mui/icons-material/SupervisedUserCircleOutlined';
import {
  List,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import { NavLink } from 'react-router-dom';
import { useAppSelector } from '../../hooks/redux';

const navItems = [
  { label: 'Students', to: '/students', icon: <PeopleAltOutlinedIcon />, permission: 'students.view' },
  { label: 'Guardians', to: '/guardians', icon: <SupervisedUserCircleOutlinedIcon />, permission: 'students.view' },
  { label: 'Academic Years', to: '/academic-years', icon: <CalendarMonthOutlinedIcon />, permission: 'students.view' },
  { label: 'Classes', to: '/classes', icon: <ClassOutlinedIcon />, permission: 'students.view' },
];

export function SidebarNav() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);

  return (
    <Paper
      elevation={0}
      sx={{
        p: 2,
        height: '100%',
        border: '1px solid rgba(20,33,61,0.08)',
        background: 'linear-gradient(180deg, #ffffff 0%, #f4fbf8 100%)',
      }}
    >
      <Stack spacing={2}>
        <Typography variant="h6">School ERP</Typography>
        <Typography variant="body2" color="text.secondary">
          Admin panel for the Student Information System.
        </Typography>
      </Stack>

      <List sx={{ mt: 3 }}>
        {navItems.filter((item) => permissions.includes(item.permission)).map((item) => (
          <ListItemButton
            key={item.to}
            component={NavLink}
            to={item.to}
            sx={{
              borderRadius: 3,
              mb: 1,
              '&.active': {
                backgroundColor: 'rgba(11, 110, 79, 0.1)',
                color: 'primary.main',
              },
            }}
          >
            <ListItemIcon sx={{ minWidth: 36 }}>{item.icon}</ListItemIcon>
            <ListItemText primary={item.label} />
          </ListItemButton>
        ))}
      </List>
    </Paper>
  );
}
