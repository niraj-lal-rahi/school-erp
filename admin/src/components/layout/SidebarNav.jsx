import CalendarMonthOutlinedIcon from '@mui/icons-material/CalendarMonthOutlined';
import ClassOutlinedIcon from '@mui/icons-material/ClassOutlined';
import DashboardOutlinedIcon from '@mui/icons-material/DashboardOutlined';
import ExpandLessOutlinedIcon from '@mui/icons-material/ExpandLessOutlined';
import ExpandMoreOutlinedIcon from '@mui/icons-material/ExpandMoreOutlined';
import LibraryBooksOutlinedIcon from '@mui/icons-material/LibraryBooksOutlined';
import MenuBookOutlinedIcon from '@mui/icons-material/MenuBookOutlined';
import PeopleAltOutlinedIcon from '@mui/icons-material/PeopleAltOutlined';
import PaletteOutlinedIcon from '@mui/icons-material/PaletteOutlined';
import PlaylistAddCheckOutlinedIcon from '@mui/icons-material/PlaylistAddCheckOutlined';
import PersonAddAltOutlinedIcon from '@mui/icons-material/PersonAddAltOutlined';
import RecentActorsOutlinedIcon from '@mui/icons-material/RecentActorsOutlined';
import SchoolOutlinedIcon from '@mui/icons-material/SchoolOutlined';
import SupervisedUserCircleOutlinedIcon from '@mui/icons-material/SupervisedUserCircleOutlined';
import TimelineOutlinedIcon from '@mui/icons-material/TimelineOutlined';
import TodayOutlinedIcon from '@mui/icons-material/TodayOutlined';
import ViewKanbanOutlinedIcon from '@mui/icons-material/ViewKanbanOutlined';
import WorkOutlineOutlinedIcon from '@mui/icons-material/WorkOutlineOutlined';
import BadgeOutlinedIcon from '@mui/icons-material/BadgeOutlined';
import AccountBalanceOutlinedIcon from '@mui/icons-material/AccountBalanceOutlined';
import AssignmentTurnedInOutlinedIcon from '@mui/icons-material/AssignmentTurnedInOutlined';
import BusinessCenterOutlinedIcon from '@mui/icons-material/BusinessCenterOutlined';
import CurrencyRupeeOutlinedIcon from '@mui/icons-material/CurrencyRupeeOutlined';
import DescriptionOutlinedIcon from '@mui/icons-material/DescriptionOutlined';
import PaymentsOutlinedIcon from '@mui/icons-material/PaymentsOutlined';
import ReceiptLongOutlinedIcon from '@mui/icons-material/ReceiptLongOutlined';
import EventAvailableOutlinedIcon from '@mui/icons-material/EventAvailableOutlined';
import FactCheckOutlinedIcon from '@mui/icons-material/FactCheckOutlined';
import HailOutlinedIcon from '@mui/icons-material/HailOutlined';
import InputOutlinedIcon from '@mui/icons-material/InputOutlined';
import ManageHistoryOutlinedIcon from '@mui/icons-material/ManageHistoryOutlined';
import MeetingRoomOutlinedIcon from '@mui/icons-material/MeetingRoomOutlined';
import ViewWeekOutlinedIcon from '@mui/icons-material/ViewWeekOutlined';
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
];

const studentManagementChildren = [
  { label: 'Students', to: '/students', icon: <PeopleAltOutlinedIcon />, permission: 'students.view' },
  { label: 'Admissions', to: '/student-admissions', icon: <PersonAddAltOutlinedIcon />, permission: 'students.view' },
  { label: 'Enrollments', to: '/student-enrollments', icon: <RecentActorsOutlinedIcon />, permission: 'students.view' },
  { label: 'Guardians', to: '/guardians', icon: <SupervisedUserCircleOutlinedIcon />, permission: 'students.view' },
  { label: 'Student Categories', to: '/student-categories', icon: <LibraryBooksOutlinedIcon />, permission: 'students.view' },
  { label: 'Student Houses', to: '/student-houses', icon: <PaletteOutlinedIcon />, permission: 'students.view' },
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

const hrChildren = [
  { label: 'Staff Directory', to: '/hr/staff', icon: <BadgeOutlinedIcon />, permission: 'hr.view' },
  { label: 'Departments', to: '/hr/departments', icon: <BusinessCenterOutlinedIcon />, permission: 'hr.view' },
  { label: 'Designations', to: '/hr/designations', icon: <WorkOutlineOutlinedIcon />, permission: 'hr.view' },
  { label: 'Attendance', to: '/hr/staff-attendance', icon: <AssignmentTurnedInOutlinedIcon />, permission: 'hr.view' },
  { label: 'Leave Types', to: '/hr/leave-types', icon: <TodayOutlinedIcon />, permission: 'hr.view' },
  { label: 'Leave Applications', to: '/hr/leave-applications', icon: <DescriptionOutlinedIcon />, permission: 'hr.view' },
  { label: 'Leave Balances', to: '/hr/leave-balances', icon: <TimelineOutlinedIcon />, permission: 'hr.view' },
  { label: 'Salary Components', to: '/hr/salary-components', icon: <CurrencyRupeeOutlinedIcon />, permission: 'hr.view' },
  { label: 'Salary Structures', to: '/hr/salary-structures', icon: <AccountBalanceOutlinedIcon />, permission: 'hr.view' },
  { label: 'Payroll Runs', to: '/hr/payroll-runs', icon: <PaymentsOutlinedIcon />, permission: 'hr.view' },
  { label: 'Payslips', to: '/hr/payslips', icon: <DescriptionOutlinedIcon />, permission: 'hr.view' },
];

const financeChildren = [
  { label: 'Fee Categories', to: '/finance/fee-categories', icon: <LibraryBooksOutlinedIcon />, permission: 'finance.view' },
  { label: 'Expense Categories', to: '/finance/expense-categories', icon: <BusinessCenterOutlinedIcon />, permission: 'finance.view' },
  { label: 'Fee Heads', to: '/finance/fee-heads', icon: <ReceiptLongOutlinedIcon />, permission: 'finance.view' },
  { label: 'Discount Types', to: '/finance/discount-types', icon: <DescriptionOutlinedIcon />, permission: 'finance.view' },
  { label: 'Student Discounts', to: '/finance/student-discounts', icon: <DescriptionOutlinedIcon />, permission: 'finance.view' },
  { label: 'Fine Rules', to: '/finance/fine-rules', icon: <DescriptionOutlinedIcon />, permission: 'finance.view' },
  { label: 'Fee Structures', to: '/finance/fee-structures', icon: <AccountBalanceOutlinedIcon />, permission: 'finance.view' },
  { label: 'Student Fee Assignments', to: '/finance/student-fee-assignments', icon: <PaymentsOutlinedIcon />, permission: 'finance.view' },
  { label: 'Fee Installments', to: '/finance/fee-installments', icon: <AssignmentTurnedInOutlinedIcon />, permission: 'finance.view' },
  { label: 'Invoices', to: '/finance/invoices', icon: <DescriptionOutlinedIcon />, permission: 'finance.view' },
  { label: 'Fee Collection', to: '/finance/fee-collection', icon: <CurrencyRupeeOutlinedIcon />, permission: 'finance.view' },
  { label: 'Payments', to: '/finance/payments', icon: <PaymentsOutlinedIcon />, permission: 'finance.view' },
  { label: 'Receipts', to: '/finance/receipts', icon: <ReceiptLongOutlinedIcon />, permission: 'finance.view' },
  { label: 'Refunds', to: '/finance/refunds', icon: <ReceiptLongOutlinedIcon />, permission: 'finance.view' },
  { label: 'Expenses', to: '/finance/expenses', icon: <BusinessCenterOutlinedIcon />, permission: 'finance.view' },
  { label: 'Ledger Accounts', to: '/finance/ledger-accounts', icon: <AccountBalanceOutlinedIcon />, permission: 'finance.view' },
  { label: 'Ledger Entries', to: '/finance/ledger-entries', icon: <DescriptionOutlinedIcon />, permission: 'finance.view' },
  { label: 'Reports Dashboard', to: '/finance/reports', icon: <TimelineOutlinedIcon />, permission: 'finance.view' },
];

const attendanceChildren = [
  { label: 'Student Attendance (Daily)', to: '/attendance/student-daily', icon: <FactCheckOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Student Attendance (Period)', to: '/attendance/student-period', icon: <TodayOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Bulk Marking', to: '/attendance/bulk-marking', icon: <PlaylistAddCheckOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Staff Attendance', to: '/attendance/staff', icon: <BadgeOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Corrections', to: '/attendance/corrections', icon: <ManageHistoryOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Imports', to: '/attendance/imports', icon: <InputOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Holidays', to: '/attendance/holidays', icon: <HailOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Status Types', to: '/attendance/status-types', icon: <PaletteOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Reports', to: '/attendance/reports', icon: <TimelineOutlinedIcon />, permission: 'attendance.view' },
  { label: 'Summary', to: '/attendance/summary', icon: <EventAvailableOutlinedIcon />, permission: 'attendance.view' },
];

const timetableChildren = [
  { label: 'Periods', to: '/timetable/periods', icon: <TodayOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Rooms', to: '/timetable/rooms', icon: <MeetingRoomOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Versions', to: '/timetable/versions', icon: <ViewWeekOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Weekly Builder', to: '/timetable/builder', icon: <PlaylistAddCheckOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Class View', to: '/timetable/class-view', icon: <ClassOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Teacher View', to: '/timetable/teacher-view', icon: <BadgeOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Room View', to: '/timetable/room-view', icon: <MeetingRoomOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Conflict Checker', to: '/timetable/conflicts', icon: <FactCheckOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Substitutions', to: '/timetable/substitutions', icon: <ManageHistoryOutlinedIcon />, permission: 'timetable.view' },
  { label: 'Schedule Exceptions', to: '/timetable/exceptions', icon: <CalendarMonthOutlinedIcon />, permission: 'timetable.view' },
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

  const visibleStudentManagementChildren = useMemo(
    () => studentManagementChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );

  const visibleAcademicChildren = useMemo(
    () => academicManagementChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );
  const visibleHrChildren = useMemo(
    () => hrChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );
  const visibleFinanceChildren = useMemo(
    () => financeChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );
  const visibleAttendanceChildren = useMemo(
    () => attendanceChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );
  const visibleTimetableChildren = useMemo(
    () => timetableChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );

  const sisRouteActive = [
    '/students',
    '/student-admissions',
    '/student-enrollments',
    '/guardians',
    '/student-categories',
    '/student-houses',
    '/academic-years',
    '/classes',
    '/sections',
  ].some((path) => location.pathname.startsWith(path));
  const academicRouteActive = location.pathname.startsWith('/academic-management');
  const hrRouteActive = location.pathname.startsWith('/hr');
  const financeRouteActive = location.pathname.startsWith('/finance');
  const attendanceRouteActive = location.pathname.startsWith('/attendance');
  const timetableRouteActive = location.pathname.startsWith('/timetable');
  const [studentManagementOpen, setStudentManagementOpen] = useState(sisRouteActive);
  const [academicOpen, setAcademicOpen] = useState(academicRouteActive);
  const [hrOpen, setHrOpen] = useState(hrRouteActive);
  const [financeOpen, setFinanceOpen] = useState(financeRouteActive);
  const [attendanceOpen, setAttendanceOpen] = useState(attendanceRouteActive);
  const [timetableOpen, setTimetableOpen] = useState(timetableRouteActive);

  useEffect(() => {
    if (sisRouteActive) {
      setStudentManagementOpen(true);
    }
  }, [sisRouteActive]);

  useEffect(() => {
    if (academicRouteActive) {
      setAcademicOpen(true);
    }
  }, [academicRouteActive]);

  useEffect(() => {
    if (hrRouteActive) {
      setHrOpen(true);
    }
  }, [hrRouteActive]);

  useEffect(() => {
    if (financeRouteActive) {
      setFinanceOpen(true);
    }
  }, [financeRouteActive]);

  useEffect(() => {
    if (attendanceRouteActive) {
      setAttendanceOpen(true);
    }
  }, [attendanceRouteActive]);

  useEffect(() => {
    if (timetableRouteActive) {
      setTimetableOpen(true);
    }
  }, [timetableRouteActive]);

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

      {visibleCoreItems.length ? (
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
      ) : null}

      {visibleStudentManagementChildren.length || visibleAcademicChildren.length || visibleHrChildren.length || visibleFinanceChildren.length || visibleAttendanceChildren.length || visibleTimetableChildren.length ? (
        <Stack spacing={1} sx={{ mt: 3 }}>
          <Divider />
          <Typography variant="overline" color="text.secondary">
            Modules
          </Typography>
          <List disablePadding>
            {visibleStudentManagementChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setStudentManagementOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: sisRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <SchoolOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Student Management" />
                  {studentManagementOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={studentManagementOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleStudentManagementChildren.map((item) => (
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
              </>
            ) : null}

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

            {visibleHrChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setHrOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: hrRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <BadgeOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Staff & HR" />
                  {hrOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={hrOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleHrChildren.map((item) => (
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
              </>
            ) : null}

            {visibleFinanceChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setFinanceOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: financeRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <CurrencyRupeeOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Fees & Finance" />
                  {financeOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={financeOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleFinanceChildren.map((item) => (
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
              </>
            ) : null}

            {visibleAttendanceChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setAttendanceOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: attendanceRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <AssignmentTurnedInOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Attendance" />
                  {attendanceOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={attendanceOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleAttendanceChildren.map((item) => (
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
              </>
            ) : null}

            {visibleTimetableChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setTimetableOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: timetableRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <ViewWeekOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Timetable" />
                  {timetableOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={timetableOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleTimetableChildren.map((item) => (
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
              </>
            ) : null}
          </List>
        </Stack>
      ) : null}
    </Paper>
  );
}
