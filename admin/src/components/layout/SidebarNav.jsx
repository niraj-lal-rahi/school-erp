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
import DirectionsBusFilledOutlinedIcon from '@mui/icons-material/DirectionsBusFilledOutlined';
import RouteOutlinedIcon from '@mui/icons-material/RouteOutlined';
import PinDropOutlinedIcon from '@mui/icons-material/PinDropOutlined';
import LocationOnOutlinedIcon from '@mui/icons-material/LocationOnOutlined';
import LocalGasStationOutlinedIcon from '@mui/icons-material/LocalGasStationOutlined';
import ViewWeekOutlinedIcon from '@mui/icons-material/ViewWeekOutlined';
import CampaignOutlinedIcon from '@mui/icons-material/CampaignOutlined';
import EmojiEventsOutlinedIcon from '@mui/icons-material/EmojiEventsOutlined';
import ForumOutlinedIcon from '@mui/icons-material/ForumOutlined';
import NotificationsActiveOutlinedIcon from '@mui/icons-material/NotificationsActiveOutlined';
import MarkEmailReadOutlinedIcon from '@mui/icons-material/MarkEmailReadOutlined';
import AdminPanelSettingsOutlinedIcon from '@mui/icons-material/AdminPanelSettingsOutlined';
import ApartmentOutlinedIcon from '@mui/icons-material/ApartmentOutlined';
import AutorenewOutlinedIcon from '@mui/icons-material/AutorenewOutlined';
import SwitchAccountOutlinedIcon from '@mui/icons-material/SwitchAccountOutlined';
import AccountTreeOutlinedIcon from '@mui/icons-material/AccountTreeOutlined';
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

const transportChildren = [
  { label: 'Vehicles', to: '/transport/vehicles', icon: <DirectionsBusFilledOutlinedIcon />, permission: 'transport.view' },
  { label: 'Drivers', to: '/transport/drivers', icon: <BadgeOutlinedIcon />, permission: 'transport.view' },
  { label: 'Routes', to: '/transport/routes', icon: <RouteOutlinedIcon />, permission: 'transport.view' },
  { label: 'Stops', to: '/transport/stops', icon: <PinDropOutlinedIcon />, permission: 'transport.view' },
  { label: 'Allocations', to: '/transport/allocations', icon: <PeopleAltOutlinedIcon />, permission: 'transport.view' },
  { label: 'Trips', to: '/transport/trips', icon: <TodayOutlinedIcon />, permission: 'transport.view' },
  { label: 'Tracking', to: '/transport/tracking', icon: <LocationOnOutlinedIcon />, permission: 'transport.view' },
  { label: 'Maintenance', to: '/transport/maintenance', icon: <ManageHistoryOutlinedIcon />, permission: 'transport.view' },
  { label: 'Fuel Logs', to: '/transport/fuel-logs', icon: <LocalGasStationOutlinedIcon />, permission: 'transport.view' },
  { label: 'Reports', to: '/transport/reports', icon: <TimelineOutlinedIcon />, permission: 'transport.view' },
];

const communicationChildren = [
  { label: 'Announcements', to: '/communication/announcements', icon: <CampaignOutlinedIcon />, permission: 'communication.view' },
  { label: 'Create Announcement', to: '/communication/announcements/new', icon: <PersonAddAltOutlinedIcon />, permission: 'communication.view' },
  { label: 'Notices & Circulars', to: '/communication/notices', icon: <DescriptionOutlinedIcon />, permission: 'communication.view' },
  { label: 'Message Inbox', to: '/communication/messages', icon: <ForumOutlinedIcon />, permission: 'communication.view' },
  { label: 'Conversation View', to: '/communication/conversations', icon: <ForumOutlinedIcon />, permission: 'communication.view' },
  { label: 'Send Message', to: '/communication/send', icon: <MarkEmailReadOutlinedIcon />, permission: 'communication.view' },
  { label: 'Message Templates', to: '/communication/templates', icon: <LibraryBooksOutlinedIcon />, permission: 'communication.view' },
  { label: 'Scheduled Messages', to: '/communication/scheduled', icon: <TodayOutlinedIcon />, permission: 'communication.view' },
  { label: 'Communication Groups', to: '/communication/groups', icon: <PeopleAltOutlinedIcon />, permission: 'communication.view' },
  { label: 'Notification Logs', to: '/communication/notifications', icon: <NotificationsActiveOutlinedIcon />, permission: 'communication.view' },
  { label: 'Preferences', to: '/communication/preferences', icon: <PaletteOutlinedIcon />, permission: 'communication.view' },
  { label: 'Reports', to: '/communication/reports', icon: <TimelineOutlinedIcon />, permission: 'communication.view' },
];

const examinationChildren = [
  { label: 'Exam Types', to: '/exams/types', icon: <LibraryBooksOutlinedIcon />, permission: 'exams.view' },
  { label: 'Exam Setup', to: '/exams/setup', icon: <SchoolOutlinedIcon />, permission: 'exams.view' },
  { label: 'Subject Mapping', to: '/exams/subjects', icon: <MenuBookOutlinedIcon />, permission: 'exams.view' },
  { label: 'Student Enrollment', to: '/exams/enrollment', icon: <PeopleAltOutlinedIcon />, permission: 'exams.view' },
  { label: 'Marks Entry', to: '/exams/marks', icon: <FactCheckOutlinedIcon />, permission: 'exams.view' },
  { label: 'Result Processing', to: '/exams/results', icon: <TimelineOutlinedIcon />, permission: 'exams.view' },
  { label: 'Result View', to: '/exams/results-view', icon: <DescriptionOutlinedIcon />, permission: 'exams.view' },
  { label: 'Merit List', to: '/exams/merit-list', icon: <EmojiEventsOutlinedIcon />, permission: 'exams.view' },
  { label: 'Report Cards', to: '/exams/report-cards', icon: <ReceiptLongOutlinedIcon />, permission: 'exams.view' },
  { label: 'Revaluation', to: '/exams/revaluation', icon: <ManageHistoryOutlinedIcon />, permission: 'exams.view' },
  { label: 'Grading Systems', to: '/exams/grading-systems', icon: <PaletteOutlinedIcon />, permission: 'exams.view' },
];

const reportsChildren = [
  { label: 'Dashboard', to: '/reports/dashboard', icon: <DashboardOutlinedIcon />, permission: 'reports.view' },
  { label: 'Reports List', to: '/reports/list', icon: <DescriptionOutlinedIcon />, permission: 'reports.view' },
  { label: 'Run Report', to: '/reports/run', icon: <PlaylistAddCheckOutlinedIcon />, permission: 'reports.run' },
  { label: 'Results Table', to: '/reports/results', icon: <FactCheckOutlinedIcon />, permission: 'reports.view' },
  { label: 'Saved Reports', to: '/reports/saved', icon: <LibraryBooksOutlinedIcon />, permission: 'reports.view' },
  { label: 'Schedule Reports', to: '/reports/schedules', icon: <TodayOutlinedIcon />, permission: 'reports.view' },
  { label: 'Exports', to: '/reports/exports', icon: <ReceiptLongOutlinedIcon />, permission: 'reports.export' },
  { label: 'Custom Builder', to: '/reports/custom-builder', icon: <TimelineOutlinedIcon />, permission: 'reports.manage' },
];

const portalChildren = [
  { label: 'Portal Home', to: '/portal', icon: <SchoolOutlinedIcon />, permission: 'portal.view' },
  { label: 'Unified Dashboard', to: '/portal/dashboard', icon: <DashboardOutlinedIcon />, permission: 'portal.view' },
  { label: 'Profile Switcher', to: '/portal/switcher', icon: <SwitchAccountOutlinedIcon />, permission: 'portal.view' },
  { label: 'Student Overview', to: '/portal/overview', icon: <PeopleAltOutlinedIcon />, permission: 'portal.view' },
  { label: 'Attendance', to: '/portal/attendance', icon: <AssignmentTurnedInOutlinedIcon />, permission: 'portal.view' },
  { label: 'Fees', to: '/portal/fees', icon: <CurrencyRupeeOutlinedIcon />, permission: 'portal.view' },
  { label: 'Results', to: '/portal/results', icon: <EmojiEventsOutlinedIcon />, permission: 'portal.view' },
  { label: 'Timetable', to: '/portal/timetable', icon: <ViewWeekOutlinedIcon />, permission: 'portal.view' },
  { label: 'Assignments', to: '/portal/assignments', icon: <LibraryBooksOutlinedIcon />, permission: 'portal.view' },
  { label: 'Transport', to: '/portal/transport', icon: <DirectionsBusFilledOutlinedIcon />, permission: 'portal.view' },
  { label: 'Documents', to: '/portal/documents', icon: <DescriptionOutlinedIcon />, permission: 'portal.view' },
  { label: 'Announcements & Messages', to: '/portal/messages', icon: <ForumOutlinedIcon />, permission: 'portal.view' },
  { label: 'Notifications', to: '/portal/notifications', icon: <NotificationsActiveOutlinedIcon />, permission: 'portal.view' },
  { label: 'Profile Settings', to: '/portal/settings', icon: <PaletteOutlinedIcon />, permission: 'portal.view' },
];

const rbacChildren = [
  { label: 'Roles List', to: '/rbac/roles', icon: <AdminPanelSettingsOutlinedIcon />, permission: 'rbac.view' },
  { label: 'Create / Edit Role', to: '/rbac/roles/new', icon: <PersonAddAltOutlinedIcon />, permission: 'rbac.manage' },
  { label: 'Permission Matrix', to: '/rbac/permissions/matrix', icon: <ViewKanbanOutlinedIcon />, permission: 'rbac.manage' },
  { label: 'Assign Roles to Users', to: '/rbac/users/assign', icon: <PeopleAltOutlinedIcon />, permission: 'rbac.manage' },
  { label: 'Permissions List', to: '/rbac/permissions', icon: <LibraryBooksOutlinedIcon />, permission: 'rbac.view' },
  { label: 'My Permissions', to: '/rbac/me', icon: <FactCheckOutlinedIcon />, permission: 'rbac.view' },
  { label: 'Audit Logs', to: '/rbac/audit-logs', icon: <TimelineOutlinedIcon />, permission: 'rbac.manage' },
];

const saasChildren = [
  { label: 'SaaS Dashboard', to: '/saas/dashboard', icon: <DashboardOutlinedIcon />, permission: 'saas.view' },
  { label: 'Tenant List', to: '/saas/tenants', icon: <ApartmentOutlinedIcon />, permission: 'saas.view' },
  { label: 'Create Tenant', to: '/saas/tenants/new', icon: <PersonAddAltOutlinedIcon />, permission: 'saas.manage' },
  { label: 'School Profile', to: '/saas/profile', icon: <SchoolOutlinedIcon />, permission: 'saas.view' },
  { label: 'Subscription Plans', to: '/saas/plans', icon: <PaymentsOutlinedIcon />, permission: 'saas.manage' },
  { label: 'Plan Feature Matrix', to: '/saas/plan-features', icon: <ViewKanbanOutlinedIcon />, permission: 'saas.manage' },
  { label: 'Tenant Subscription', to: '/saas/subscriptions', icon: <AutorenewOutlinedIcon />, permission: 'saas.manage' },
  { label: 'Tenant Usage', to: '/saas/usage', icon: <TimelineOutlinedIcon />, permission: 'saas.view' },
  { label: 'Tenant Billing', to: '/saas/billing', icon: <ReceiptLongOutlinedIcon />, permission: 'saas.manage' },
  { label: 'Tenant Domains', to: '/saas/domains', icon: <LocationOnOutlinedIcon />, permission: 'saas.view' },
  { label: 'SaaS Onboarding', to: '/saas/onboarding', icon: <CampaignOutlinedIcon />, permission: 'saas.manage' },
];

const paymentsChildren = [
  { label: 'Gateway Settings', to: '/payments/gateways', icon: <PaymentsOutlinedIcon />, permission: 'finance.view' },
  { label: 'Payment Transactions', to: '/payments/transactions', icon: <ReceiptLongOutlinedIcon />, permission: 'finance.view' },
  { label: 'Initiate Payment', to: '/payments/initiate', icon: <PersonAddAltOutlinedIcon />, permission: 'finance.view' },
  { label: 'UPI Verification', to: '/payments/upi-verification', icon: <FactCheckOutlinedIcon />, permission: 'finance.view' },
  { label: 'Manual Approval', to: '/payments/manual-approval', icon: <AssignmentTurnedInOutlinedIcon />, permission: 'finance.view' },
  { label: 'Refund Management', to: '/payments/refunds', icon: <AutorenewOutlinedIcon />, permission: 'finance.view' },
  { label: 'Webhook Logs', to: '/payments/webhook-logs', icon: <NotificationsActiveOutlinedIcon />, permission: 'finance.view' },
  { label: 'Reconciliation', to: '/payments/reconciliation', icon: <PlaylistAddCheckOutlinedIcon />, permission: 'finance.view' },
  { label: 'Payment Reports', to: '/payments/reports', icon: <TimelineOutlinedIcon />, permission: 'finance.view' },
];

const workflowsChildren = [
  { label: 'Workflow Definitions', to: '/workflows/definitions', icon: <AccountTreeOutlinedIcon />, permission: 'workflows.view' },
  { label: 'Workflow Builder', to: '/workflows/builder', icon: <PlaylistAddCheckOutlinedIcon />, permission: 'workflows.manage' },
  { label: 'Workflow Instances', to: '/workflows/instances', icon: <TimelineOutlinedIcon />, permission: 'workflows.view' },
  { label: 'Pending Approvals', to: '/workflows/approvals', icon: <AssignmentTurnedInOutlinedIcon />, permission: 'workflows.approve' },
  { label: 'Automation Rules', to: '/workflows/automations', icon: <AutorenewOutlinedIcon />, permission: 'workflows.manage' },
  { label: 'Automation Run Logs', to: '/workflows/automation-runs', icon: <FactCheckOutlinedIcon />, permission: 'workflows.view' },
  { label: 'Reminder Rules', to: '/workflows/reminders', icon: <NotificationsActiveOutlinedIcon />, permission: 'workflows.manage' },
  { label: 'Workflow Reports', to: '/workflows/reports', icon: <TimelineOutlinedIcon />, permission: 'workflows.view' },
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
  const roles = useAppSelector((state) => state.auth.user?.roles || []);
  const location = useLocation();
  const roleCodes = useMemo(() => roles.map((role) => role.code || role.slug).filter(Boolean), [roles]);
  const isSuperAdmin = roleCodes.includes('super_admin');
  const isTenantAdmin = roleCodes.includes('tenant_admin') || roleCodes.includes('school-admin');
  const isAccountant = roleCodes.includes('accountant');

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
  const visibleTransportChildren = useMemo(
    () => transportChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );
  const visibleCommunicationChildren = useMemo(
    () => communicationChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );
  const visibleExaminationChildren = useMemo(
    () => examinationChildren.filter((item) => permissions.includes(item.permission)),
    [permissions],
  );
  const visibleReportsChildren = useMemo(
    () => reportsChildren.filter((item) => permissions.includes(item.permission) || (item.permission === 'reports.view' && permissions.includes('reports.manage'))),
    [permissions],
  );
  const visiblePortalChildren = useMemo(
    () => portalChildren.filter((item) => permissions.includes(item.permission) || (item.permission === 'portal.view' && permissions.includes('portal.manage'))),
    [permissions],
  );
  const visibleRbacChildren = useMemo(
    () => rbacChildren.filter((item) => permissions.includes(item.permission) || (item.permission === 'rbac.view' && permissions.includes('rbac.manage'))),
    [permissions],
  );
  const visibleSaasChildren = useMemo(
    () => saasChildren.filter((item) => (
      isSuperAdmin
      || (item.permission === 'saas.view' && isTenantAdmin)
      || permissions.includes(item.permission)
      || (item.permission === 'saas.view' && permissions.includes('saas.manage'))
    )),
    [isSuperAdmin, isTenantAdmin, permissions],
  );
  const visiblePaymentsChildren = useMemo(
    () => paymentsChildren.filter((item) => (
      isSuperAdmin
      || isTenantAdmin
      || isAccountant
      || permissions.includes(item.permission)
      || permissions.includes('finance.manage')
      || permissions.includes('payments.view')
      || permissions.includes('payments.manage')
    )),
    [isSuperAdmin, isTenantAdmin, isAccountant, permissions],
  );
  const visibleWorkflowsChildren = useMemo(
    () => workflowsChildren.filter((item) => (
      isSuperAdmin
      || isTenantAdmin
      || permissions.includes(item.permission)
      || (item.permission === 'workflows.view' && permissions.includes('workflows.manage'))
      || (item.permission === 'workflows.approve' && permissions.includes('workflows.manage'))
    )),
    [isSuperAdmin, isTenantAdmin, permissions],
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
  const transportRouteActive = location.pathname.startsWith('/transport');
  const communicationRouteActive = location.pathname.startsWith('/communication');
  const examinationRouteActive = location.pathname.startsWith('/exams');
  const reportsRouteActive = location.pathname.startsWith('/reports');
  const portalRouteActive = location.pathname.startsWith('/portal');
  const rbacRouteActive = location.pathname.startsWith('/rbac');
  const saasRouteActive = location.pathname.startsWith('/saas');
  const paymentsRouteActive = location.pathname.startsWith('/payments');
  const workflowsRouteActive = location.pathname.startsWith('/workflows');
  const [studentManagementOpen, setStudentManagementOpen] = useState(sisRouteActive);
  const [academicOpen, setAcademicOpen] = useState(academicRouteActive);
  const [hrOpen, setHrOpen] = useState(hrRouteActive);
  const [financeOpen, setFinanceOpen] = useState(financeRouteActive);
  const [attendanceOpen, setAttendanceOpen] = useState(attendanceRouteActive);
  const [timetableOpen, setTimetableOpen] = useState(timetableRouteActive);
  const [transportOpen, setTransportOpen] = useState(transportRouteActive);
  const [communicationOpen, setCommunicationOpen] = useState(communicationRouteActive);
  const [examinationOpen, setExaminationOpen] = useState(examinationRouteActive);
  const [reportsOpen, setReportsOpen] = useState(reportsRouteActive);
  const [portalOpen, setPortalOpen] = useState(portalRouteActive);
  const [rbacOpen, setRbacOpen] = useState(rbacRouteActive);
  const [saasOpen, setSaasOpen] = useState(saasRouteActive);
  const [paymentsOpen, setPaymentsOpen] = useState(paymentsRouteActive);
  const [workflowsOpen, setWorkflowsOpen] = useState(workflowsRouteActive);

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

  useEffect(() => {
    if (transportRouteActive) {
      setTransportOpen(true);
    }
  }, [transportRouteActive]);

  useEffect(() => {
    if (communicationRouteActive) {
      setCommunicationOpen(true);
    }
  }, [communicationRouteActive]);

  useEffect(() => {
    if (examinationRouteActive) {
      setExaminationOpen(true);
    }
  }, [examinationRouteActive]);

  useEffect(() => {
    if (reportsRouteActive) {
      setReportsOpen(true);
    }
  }, [reportsRouteActive]);

  useEffect(() => {
    if (portalRouteActive) {
      setPortalOpen(true);
    }
  }, [portalRouteActive]);

  useEffect(() => {
    if (rbacRouteActive) {
      setRbacOpen(true);
    }
  }, [rbacRouteActive]);

  useEffect(() => {
    if (saasRouteActive) {
      setSaasOpen(true);
    }
  }, [saasRouteActive]);

  useEffect(() => {
    if (paymentsRouteActive) {
      setPaymentsOpen(true);
    }
  }, [paymentsRouteActive]);

  useEffect(() => {
    if (workflowsRouteActive) {
      setWorkflowsOpen(true);
    }
  }, [workflowsRouteActive]);

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

      {visibleStudentManagementChildren.length || visibleAcademicChildren.length || visibleHrChildren.length || visibleFinanceChildren.length || visibleAttendanceChildren.length || visibleTimetableChildren.length || visibleTransportChildren.length || visibleCommunicationChildren.length || visibleExaminationChildren.length || visibleReportsChildren.length || visiblePortalChildren.length || visibleRbacChildren.length || visibleSaasChildren.length || visiblePaymentsChildren.length || visibleWorkflowsChildren.length ? (
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

            {visibleTransportChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setTransportOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: transportRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <DirectionsBusFilledOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Transport" />
                  {transportOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={transportOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleTransportChildren.map((item) => (
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

            {visibleCommunicationChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setCommunicationOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: communicationRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <CampaignOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Communication" />
                  {communicationOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={communicationOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleCommunicationChildren.map((item) => (
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

            {visibleExaminationChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setExaminationOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: examinationRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <EmojiEventsOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Examinations" />
                  {examinationOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={examinationOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleExaminationChildren.map((item) => (
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

            {visibleReportsChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setReportsOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: reportsRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <TimelineOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Reports & Analytics" />
                  {reportsOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={reportsOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleReportsChildren.map((item) => (
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

            {visiblePortalChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setPortalOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: portalRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <SwitchAccountOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Parent & Student Portal" />
                  {portalOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={portalOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visiblePortalChildren.map((item) => (
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

            {visibleRbacChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setRbacOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: rbacRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <AdminPanelSettingsOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="RBAC & Access" />
                  {rbacOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={rbacOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleRbacChildren.map((item) => (
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

            {visibleSaasChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setSaasOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: saasRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <ApartmentOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="SaaS Tenancy" />
                  {saasOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={saasOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleSaasChildren.map((item) => (
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

            {visiblePaymentsChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setPaymentsOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: paymentsRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <PaymentsOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Payments & Gateways" />
                  {paymentsOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={paymentsOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visiblePaymentsChildren.map((item) => (
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

            {visibleWorkflowsChildren.length ? (
              <>
                <ListItemButton
                  onClick={() => setWorkflowsOpen((current) => !current)}
                  sx={{
                    ...itemStyles(),
                    backgroundColor: workflowsRouteActive ? 'rgba(11, 110, 79, 0.06)' : 'transparent',
                  }}
                >
                  <ListItemIcon sx={{ minWidth: 36 }}>
                    <AccountTreeOutlinedIcon />
                  </ListItemIcon>
                  <ListItemText primary="Workflow & Automation" />
                  {workflowsOpen ? <ExpandLessOutlinedIcon /> : <ExpandMoreOutlinedIcon />}
                </ListItemButton>

                <Collapse in={workflowsOpen} timeout="auto" unmountOnExit>
                  <List disablePadding sx={{ mt: 0.5 }}>
                    {visibleWorkflowsChildren.map((item) => (
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
