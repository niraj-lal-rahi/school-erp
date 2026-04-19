import CalendarMonthOutlinedIcon from '@mui/icons-material/CalendarMonthOutlined';
import ClassOutlinedIcon from '@mui/icons-material/ClassOutlined';
import DashboardOutlinedIcon from '@mui/icons-material/DashboardOutlined';
import ExpandLessOutlinedIcon from '@mui/icons-material/ExpandLessOutlined';
import ExpandMoreOutlinedIcon from '@mui/icons-material/ExpandMoreOutlined';
import LibraryBooksOutlinedIcon from '@mui/icons-material/LibraryBooksOutlined';
import MenuBookOutlinedIcon from '@mui/icons-material/MenuBookOutlined';
import PeopleAltOutlinedIcon from '@mui/icons-material/PeopleAltOutlined';
import PlaylistAddCheckOutlinedIcon from '@mui/icons-material/PlaylistAddCheckOutlined';
import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import SupervisedUserCircleOutlinedIcon from '@mui/icons-material/SupervisedUserCircleOutlined';
import TimelineOutlinedIcon from '@mui/icons-material/TimelineOutlined';
import TodayOutlinedIcon from '@mui/icons-material/TodayOutlined';
import ViewKanbanOutlinedIcon from '@mui/icons-material/ViewKanbanOutlined';
import WorkOutlineOutlinedIcon from '@mui/icons-material/WorkOutlineOutlined';
import {
  Collapse,
  Divider,
  List,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  Paper,
  Stack,
  Typography,
} from '@mui/material';
import { useEffect, useMemo, useState } from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { useAppSelector } from '../../hooks/redux';

const coreItems = [
  { label: 'Dashboard', to: '/dashboard', icon: <DashboardOutlinedIcon />, permission: 'students.view' },
  { label: 'Students', to: '/students', icon: <PeopleAltOutlinedIcon />, permission: 'students.view' },
  { label: 'Guardians', to: '/guardians', icon: <SupervisedUserCircleOutlinedIcon />, permission: 'students.view' },
  { label: 'Academic Years', to: '/academic-years', icon: <CalendarMonthOutlinedIcon />, permission: 'students.view' },
  { label: 'Classes', to: '/classes', icon: <ClassOutlinedIcon />, permission: 'students.view' },
  { label: 'Sections', to: '/sections', icon: <ViewKanbanOutlinedIcon />, permission: 'students.view' },
];

const academicManagementChildren = [
  { label: 'Academic Years', to: '/academic-management/academic-years', icon: <SchoolOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Terms', to: '/academic-management/terms', icon: <TimelineOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Classes', to: '/academic-management/classes', icon: <ClassOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Sections', to: '/academic-management/sections', icon: <ViewKanbanOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Subjects', to: '/academic-management/subjects', icon: <MenuBookOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Class Subjects', to: '/academic-management/class-subjects', icon: <LibraryBooksOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Teacher Assignments', to: '/academic-management/teacher-assignments', icon: <WorkOutlineOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Curriculum', to: '/academic-management/curriculum', icon: <PlaylistAddCheckOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Lesson Plans', to: '/academic-management/lesson-plans', icon: <TodayOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Assignments', to: '/academic-management/assignments', icon: <LibraryBooksOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Academic Calendar', to: '/academic-management/academic-calendar', icon: <CalendarMonthOutlinedIcon />, permission: 'academic-management.view' },
  { label: 'Grading Structures', to: '/academic-management/grading-structures', icon: <TimelineOutlinedIcon />, permission: 'academic-management.view' },
];

function itemStyles(isChild = false) {
  return {
    borderRadius: 3,
    mb: 1,
    pl: isChild ? 4 : 2,
    '&.active': {
      backgroundColor: 'rgba(11, 110, 79, 0.1)',
      color: 'primary.main',
    },
  };
}

export function SidebarNav() {
  const permissions = useAppSelector((state) => state.auth.user?.permissions || []);
  const location = useLocation();

  const visibleCoreItems = useMemo(
    () => coreItems.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );

  const visibleAcademicChildren = useMemo(
    () => academicManagementChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );

  const academicRouteActive = location.pathname.startsWith('/academic-management');
  const [academicOpen, setAcademicOpen] = useState(academicRouteActive);

  useEffect(() => {
    if (academicRouteActive) {
      setAcademicOpen(true);
    }
  }, [academicRouteActive]);

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
          Admin panel for SIS and academic operations.
        </Typography>
      </Stack>

      <Stack spacing={1} sx={{ mt: 3 }}>
        <Typography variant="overline" color="text.secondary">
          Core
        </Typography>
        <List disablePadding>
          {visibleCoreItems.map((item) => (
            <ListItemButton
              key={item.to}
              component={NavLink}
              to={item.to}
              sx={itemStyles()}
            >
              <ListItemIcon sx={{ minWidth: 36 }}>{item.icon}</ListItemIcon>
              <ListItemText primary={item.label} />
            </ListItemButton>
          ))}
        </List>
      </Stack>

      {visibleAcademicChildren.length ? (
        <Stack spacing={1} sx={{ mt: 3 }}>
          <Divider />
          <Typography variant="overline" color="text.secondary">
            Modules
          </Typography>
          <List disablePadding>
            <ListItemButton
              onClick={() => setAcademicOpen((current) => !current)}
              sx={{
                ...itemStyles(),
                backgroundColor: academicRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
              }}
            >
              <ListItemIcon sx={{ minWidth: 36 }}>
                <SchoolOutlinedIcon />
              </ListItemIcon>
              <ListItemText primary="Academic Management" />
              {academicOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
            </ListItemButton>

            <Collapse in={academicOpen} timeout="auto" unmountOnExit>
              <List disablePadding sx={{ mt: 0.5 }}>
                {visibleAcademicChildren.map((item) => (
                  <ListItemButton
                    key={item.to}
                    component={NavLink}
                    to={item.to}
                    sx={itemStyles(true)}
                  >
                    <ListItemIcon sx={{ minWidth: 36 }}>{item.icon}</ListItemIcon>
                    <ListItemText primary={item.label} />
                  </ListItemButton>
                ))}
              </List>
            </Collapse>
          </List>
        </Stack>
      ) : null}
    </Paper>
  );
}
