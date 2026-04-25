import { Suspense, lazy } from 'react';
import { createBrowserRouter, Navigate } from 'react-router-dom';
import { FullScreenLoader } from '../components/common/FullScreenLoader';
import { RequireAuth } from '../components/common/RequireAuth';

const AdminLayout = lazy(() => import('../layouts/AdminLayout').then((module) => ({ default: module.AdminLayout })));
const LoginPage = lazy(() => import('../features/auth/LoginPage').then((module) => ({ default: module.LoginPage })));
const DashboardPage = lazy(() => import('../features/dashboard/DashboardPage').then((module) => ({ default: module.DashboardPage })));
const StudentListPage = lazy(() => import('../features/students/pages/StudentListPage').then((module) => ({ default: module.StudentListPage })));
const StudentCreatePage = lazy(() => import('../features/students/pages/StudentCreatePage').then((module) => ({ default: module.StudentCreatePage })));
const StudentEditPage = lazy(() => import('../features/students/pages/StudentEditPage').then((module) => ({ default: module.StudentEditPage })));
const StudentProfilePage = lazy(() => import('../features/students/pages/StudentProfilePage').then((module) => ({ default: module.StudentProfilePage })));
const AdmissionsPage = lazy(() => import('../features/admissions/pages/AdmissionsPage').then((module) => ({ default: module.AdmissionsPage })));
const AdmissionReviewPage = lazy(() => import('../features/admissions/pages/AdmissionReviewPage').then((module) => ({ default: module.AdmissionReviewPage })));
const EnrollmentsPage = lazy(() => import('../features/enrollments/pages/EnrollmentsPage').then((module) => ({ default: module.EnrollmentsPage })));
const GuardiansPage = lazy(() => import('../features/masterData/pages/GuardiansPage').then((module) => ({ default: module.GuardiansPage })));
const StudentCategoriesPage = lazy(() => import('../features/masterData/pages/StudentCategoriesPage').then((module) => ({ default: module.StudentCategoriesPage })));
const StudentHousesPage = lazy(() => import('../features/masterData/pages/StudentHousesPage').then((module) => ({ default: module.StudentHousesPage })));
const AcademicYearsPage = lazy(() => import('../features/masterData/pages/AcademicYearsPage').then((module) => ({ default: module.AcademicYearsPage })));
const ClassesPage = lazy(() => import('../features/masterData/pages/ClassesPage').then((module) => ({ default: module.ClassesPage })));
const SectionsPage = lazy(() => import('../features/masterData/pages/SectionsPage').then((module) => ({ default: module.SectionsPage })));
const AcademicManagementAcademicYearsPage = lazy(() => import('../features/academicManagement/pages/AcademicYearsPage').then((module) => ({ default: module.AcademicYearsPage })));
const TermsPage = lazy(() => import('../features/academicManagement/pages/TermsPage').then((module) => ({ default: module.TermsPage })));
const AcademicManagementClassesPage = lazy(() => import('../features/academicManagement/pages/ClassesPage').then((module) => ({ default: module.ClassesPage })));
const AcademicManagementSectionsPage = lazy(() => import('../features/academicManagement/pages/SectionsPage').then((module) => ({ default: module.SectionsPage })));
const SubjectsPage = lazy(() => import('../features/academicManagement/pages/SubjectsPage').then((module) => ({ default: module.SubjectsPage })));
const ClassSubjectsPage = lazy(() => import('../features/academicManagement/pages/ClassSubjectsPage').then((module) => ({ default: module.ClassSubjectsPage })));
const TeacherAssignmentsPage = lazy(() => import('../features/academicManagement/pages/TeacherAssignmentsPage').then((module) => ({ default: module.TeacherAssignmentsPage })));
const CurriculumPage = lazy(() => import('../features/academicManagement/pages/CurriculumPage').then((module) => ({ default: module.CurriculumPage })));
const LessonPlansPage = lazy(() => import('../features/academicManagement/pages/LessonPlansPage').then((module) => ({ default: module.LessonPlansPage })));
const AssignmentsPage = lazy(() => import('../features/academicManagement/pages/AssignmentsPage').then((module) => ({ default: module.AssignmentsPage })));
const AcademicCalendarPage = lazy(() => import('../features/academicManagement/pages/AcademicCalendarPage').then((module) => ({ default: module.AcademicCalendarPage })));
const GradingStructuresPage = lazy(() => import('../features/academicManagement/pages/GradingStructuresPage').then((module) => ({ default: module.GradingStructuresPage })));
const StaffDirectoryPage = lazy(() => import('../features/hr/pages/StaffDirectoryPage').then((module) => ({ default: module.StaffDirectoryPage })));
const StaffProfilePage = lazy(() => import('../features/hr/pages/StaffProfilePage').then((module) => ({ default: module.StaffProfilePage })));
const DepartmentsPage = lazy(() => import('../features/hr/pages/DepartmentsPage').then((module) => ({ default: module.DepartmentsPage })));
const DesignationsPage = lazy(() => import('../features/hr/pages/DesignationsPage').then((module) => ({ default: module.DesignationsPage })));
const StaffAttendancePage = lazy(() => import('../features/hr/pages/StaffAttendancePage').then((module) => ({ default: module.StaffAttendancePage })));
const LeaveTypesPage = lazy(() => import('../features/hr/pages/LeaveTypesPage').then((module) => ({ default: module.LeaveTypesPage })));
const LeaveApplicationsPage = lazy(() => import('../features/hr/pages/LeaveApplicationsPage').then((module) => ({ default: module.LeaveApplicationsPage })));
const LeaveBalancesPage = lazy(() => import('../features/hr/pages/LeaveBalancesPage').then((module) => ({ default: module.LeaveBalancesPage })));
const SalaryComponentsPage = lazy(() => import('../features/hr/pages/SalaryComponentsPage').then((module) => ({ default: module.SalaryComponentsPage })));
const SalaryStructuresPage = lazy(() => import('../features/hr/pages/SalaryStructuresPage').then((module) => ({ default: module.SalaryStructuresPage })));
const PayrollRunsPage = lazy(() => import('../features/hr/pages/PayrollRunsPage').then((module) => ({ default: module.PayrollRunsPage })));
const PayslipsPage = lazy(() => import('../features/hr/pages/PayslipsPage').then((module) => ({ default: module.PayslipsPage })));
const FeeCategoriesPage = lazy(() => import('../features/finance/pages/FeeCategoriesPage').then((module) => ({ default: module.FeeCategoriesPage })));
const ExpenseCategoriesPage = lazy(() => import('../features/finance/pages/ExpenseCategoriesPage').then((module) => ({ default: module.ExpenseCategoriesPage })));
const FeeHeadsPage = lazy(() => import('../features/finance/pages/FeeHeadsPage').then((module) => ({ default: module.FeeHeadsPage })));
const DiscountTypesPage = lazy(() => import('../features/finance/pages/DiscountTypesPage').then((module) => ({ default: module.DiscountTypesPage })));
const StudentDiscountsPage = lazy(() => import('../features/finance/pages/StudentDiscountsPage').then((module) => ({ default: module.StudentDiscountsPage })));
const FineRulesPage = lazy(() => import('../features/finance/pages/FineRulesPage').then((module) => ({ default: module.FineRulesPage })));
const FeeStructuresPage = lazy(() => import('../features/finance/pages/FeeStructuresPage').then((module) => ({ default: module.FeeStructuresPage })));
const StudentFeeAssignmentsPage = lazy(() => import('../features/finance/pages/StudentFeeAssignmentsPage').then((module) => ({ default: module.StudentFeeAssignmentsPage })));
const FeeInstallmentsPage = lazy(() => import('../features/finance/pages/FeeInstallmentsPage').then((module) => ({ default: module.FeeInstallmentsPage })));
const InvoicesPage = lazy(() => import('../features/finance/pages/InvoicesPage').then((module) => ({ default: module.InvoicesPage })));
const FeeCollectionPage = lazy(() => import('../features/finance/pages/FeeCollectionPage').then((module) => ({ default: module.FeeCollectionPage })));
const PaymentsPage = lazy(() => import('../features/finance/pages/PaymentsPage').then((module) => ({ default: module.PaymentsPage })));
const ReceiptsPage = lazy(() => import('../features/finance/pages/ReceiptsPage').then((module) => ({ default: module.ReceiptsPage })));
const RefundsPage = lazy(() => import('../features/finance/pages/RefundsPage').then((module) => ({ default: module.RefundsPage })));
const ExpensesPage = lazy(() => import('../features/finance/pages/ExpensesPage').then((module) => ({ default: module.ExpensesPage })));
const LedgerAccountsPage = lazy(() => import('../features/finance/pages/LedgerAccountsPage').then((module) => ({ default: module.LedgerAccountsPage })));
const LedgerEntriesPage = lazy(() => import('../features/finance/pages/LedgerEntriesPage').then((module) => ({ default: module.LedgerEntriesPage })));
const FinanceReportsPage = lazy(() => import('../features/finance/pages/FinanceReportsPage').then((module) => ({ default: module.FinanceReportsPage })));
const StudentAttendanceDailyPage = lazy(() => import('../features/attendance/pages/StudentAttendanceDailyPage').then((module) => ({ default: module.StudentAttendanceDailyPage })));
const StudentAttendancePeriodPage = lazy(() => import('../features/attendance/pages/StudentAttendancePeriodPage').then((module) => ({ default: module.StudentAttendancePeriodPage })));
const AttendanceBulkMarkingPage = lazy(() => import('../features/attendance/pages/AttendanceBulkMarkingPage').then((module) => ({ default: module.AttendanceBulkMarkingPage })));
const AttendanceStaffPage = lazy(() => import('../features/attendance/pages/AttendanceStaffPage').then((module) => ({ default: module.AttendanceStaffPage })));
const AttendanceCorrectionsPage = lazy(() => import('../features/attendance/pages/AttendanceCorrectionsPage').then((module) => ({ default: module.AttendanceCorrectionsPage })));
const AttendanceImportsPage = lazy(() => import('../features/attendance/pages/AttendanceImportsPage').then((module) => ({ default: module.AttendanceImportsPage })));
const AttendanceHolidaysPage = lazy(() => import('../features/attendance/pages/AttendanceHolidaysPage').then((module) => ({ default: module.AttendanceHolidaysPage })));
const AttendanceStatusTypesPage = lazy(() => import('../features/attendance/pages/AttendanceStatusTypesPage').then((module) => ({ default: module.AttendanceStatusTypesPage })));
const AttendanceReportsPage = lazy(() => import('../features/attendance/pages/AttendanceReportsPage').then((module) => ({ default: module.AttendanceReportsPage })));
const AttendanceSummaryPage = lazy(() => import('../features/attendance/pages/AttendanceSummaryPage').then((module) => ({ default: module.AttendanceSummaryPage })));
const TimetablePeriodsPage = lazy(() => import('../features/timetable/pages/PeriodsPage').then((module) => ({ default: module.PeriodsPage })));
const TimetableRoomsPage = lazy(() => import('../features/timetable/pages/RoomsPage').then((module) => ({ default: module.RoomsPage })));
const TimetableVersionsPage = lazy(() => import('../features/timetable/pages/VersionsPage').then((module) => ({ default: module.VersionsPage })));
const WeeklyTimetableBuilderPage = lazy(() => import('../features/timetable/pages/WeeklyTimetableBuilderPage').then((module) => ({ default: module.WeeklyTimetableBuilderPage })));
const ClassTimetableViewPage = lazy(() => import('../features/timetable/pages/ClassTimetableViewPage').then((module) => ({ default: module.ClassTimetableViewPage })));
const TeacherTimetableViewPage = lazy(() => import('../features/timetable/pages/TeacherTimetableViewPage').then((module) => ({ default: module.TeacherTimetableViewPage })));
const RoomScheduleViewPage = lazy(() => import('../features/timetable/pages/RoomScheduleViewPage').then((module) => ({ default: module.RoomScheduleViewPage })));
const ConflictCheckerPage = lazy(() => import('../features/timetable/pages/ConflictCheckerPage').then((module) => ({ default: module.ConflictCheckerPage })));
const SubstitutionsPage = lazy(() => import('../features/timetable/pages/SubstitutionsPage').then((module) => ({ default: module.SubstitutionsPage })));
const ScheduleExceptionsPage = lazy(() => import('../features/timetable/pages/ScheduleExceptionsPage').then((module) => ({ default: module.ScheduleExceptionsPage })));

function withSuspense(element) {
  return <Suspense fallback={<FullScreenLoader />}>{element}</Suspense>;
}

export const router = createBrowserRouter([
  {
    path: '/login',
    element: withSuspense(<LoginPage />),
  },
  {
    element: <RequireAuth />,
    children: [
      {
        path: '/',
        element: withSuspense(<AdminLayout />),
        children: [
          {
            index: true,
            element: <Navigate to="/dashboard" replace />,
          },
          {
            path: 'dashboard',
            element: withSuspense(<DashboardPage />),
          },
          {
            path: 'students',
            element: withSuspense(<StudentListPage />),
          },
          {
            path: 'students/new',
            element: withSuspense(<StudentCreatePage />),
          },
          {
            path: 'students/:studentId',
            element: withSuspense(<StudentProfilePage />),
          },
          {
            path: 'students/:studentId/edit',
            element: withSuspense(<StudentEditPage />),
          },
          {
            path: 'student-admissions',
            element: withSuspense(<AdmissionsPage />),
          },
          {
            path: 'student-admissions/:admissionId/review',
            element: withSuspense(<AdmissionReviewPage />),
          },
          {
            path: 'student-enrollments',
            element: withSuspense(<EnrollmentsPage />),
          },
          {
            path: 'guardians',
            element: withSuspense(<GuardiansPage />),
          },
          {
            path: 'student-categories',
            element: withSuspense(<StudentCategoriesPage />),
          },
          {
            path: 'student-houses',
            element: withSuspense(<StudentHousesPage />),
          },
          {
            path: 'academic-years',
            element: withSuspense(<AcademicYearsPage />),
          },
          {
            path: 'classes',
            element: withSuspense(<ClassesPage />),
          },
          {
            path: 'sections',
            element: withSuspense(<SectionsPage />),
          },
          {
            path: 'academic-management/academic-years',
            element: withSuspense(<AcademicManagementAcademicYearsPage />),
          },
          {
            path: 'academic-management/terms',
            element: withSuspense(<TermsPage />),
          },
          {
            path: 'academic-management/classes',
            element: withSuspense(<AcademicManagementClassesPage />),
          },
          {
            path: 'academic-management/sections',
            element: withSuspense(<AcademicManagementSectionsPage />),
          },
          {
            path: 'academic-management/subjects',
            element: withSuspense(<SubjectsPage />),
          },
          {
            path: 'academic-management/class-subjects',
            element: withSuspense(<ClassSubjectsPage />),
          },
          {
            path: 'academic-management/teacher-assignments',
            element: withSuspense(<TeacherAssignmentsPage />),
          },
          {
            path: 'academic-management/curriculum',
            element: withSuspense(<CurriculumPage />),
          },
          {
            path: 'academic-management/lesson-plans',
            element: withSuspense(<LessonPlansPage />),
          },
          {
            path: 'academic-management/assignments',
            element: withSuspense(<AssignmentsPage />),
          },
          {
            path: 'academic-management/academic-calendar',
            element: withSuspense(<AcademicCalendarPage />),
          },
          {
            path: 'academic-management/grading-structures',
            element: withSuspense(<GradingStructuresPage />),
          },
          {
            path: 'hr/staff',
            element: withSuspense(<StaffDirectoryPage />),
          },
          {
            path: 'hr/staff/:staffId',
            element: withSuspense(<StaffProfilePage />),
          },
          {
            path: 'hr/departments',
            element: withSuspense(<DepartmentsPage />),
          },
          {
            path: 'hr/designations',
            element: withSuspense(<DesignationsPage />),
          },
          {
            path: 'hr/staff-attendance',
            element: withSuspense(<StaffAttendancePage />),
          },
          {
            path: 'hr/leave-types',
            element: withSuspense(<LeaveTypesPage />),
          },
          {
            path: 'hr/leave-applications',
            element: withSuspense(<LeaveApplicationsPage />),
          },
          {
            path: 'hr/leave-balances',
            element: withSuspense(<LeaveBalancesPage />),
          },
          {
            path: 'hr/salary-components',
            element: withSuspense(<SalaryComponentsPage />),
          },
          {
            path: 'hr/salary-structures',
            element: withSuspense(<SalaryStructuresPage />),
          },
          {
            path: 'hr/payroll-runs',
            element: withSuspense(<PayrollRunsPage />),
          },
          {
            path: 'hr/payslips',
            element: withSuspense(<PayslipsPage />),
          },
          {
            path: 'finance/fee-categories',
            element: withSuspense(<FeeCategoriesPage />),
          },
          {
            path: 'finance/expense-categories',
            element: withSuspense(<ExpenseCategoriesPage />),
          },
          {
            path: 'finance/fee-heads',
            element: withSuspense(<FeeHeadsPage />),
          },
          {
            path: 'finance/discount-types',
            element: withSuspense(<DiscountTypesPage />),
          },
          {
            path: 'finance/student-discounts',
            element: withSuspense(<StudentDiscountsPage />),
          },
          {
            path: 'finance/fine-rules',
            element: withSuspense(<FineRulesPage />),
          },
          {
            path: 'finance/fee-structures',
            element: withSuspense(<FeeStructuresPage />),
          },
          {
            path: 'finance/student-fee-assignments',
            element: withSuspense(<StudentFeeAssignmentsPage />),
          },
          {
            path: 'finance/fee-installments',
            element: withSuspense(<FeeInstallmentsPage />),
          },
          {
            path: 'finance/invoices',
            element: withSuspense(<InvoicesPage />),
          },
          {
            path: 'finance/fee-collection',
            element: withSuspense(<FeeCollectionPage />),
          },
          {
            path: 'finance/payments',
            element: withSuspense(<PaymentsPage />),
          },
          {
            path: 'finance/receipts',
            element: withSuspense(<ReceiptsPage />),
          },
          {
            path: 'finance/refunds',
            element: withSuspense(<RefundsPage />),
          },
          {
            path: 'finance/expenses',
            element: withSuspense(<ExpensesPage />),
          },
          {
            path: 'finance/ledger-accounts',
            element: withSuspense(<LedgerAccountsPage />),
          },
          {
            path: 'finance/ledger-entries',
            element: withSuspense(<LedgerEntriesPage />),
          },
          {
            path: 'finance/reports',
            element: withSuspense(<FinanceReportsPage />),
          },
          {
            path: 'attendance/student-daily',
            element: withSuspense(<StudentAttendanceDailyPage />),
          },
          {
            path: 'attendance/student-period',
            element: withSuspense(<StudentAttendancePeriodPage />),
          },
          {
            path: 'attendance/bulk-marking',
            element: withSuspense(<AttendanceBulkMarkingPage />),
          },
          {
            path: 'attendance/staff',
            element: withSuspense(<AttendanceStaffPage />),
          },
          {
            path: 'attendance/corrections',
            element: withSuspense(<AttendanceCorrectionsPage />),
          },
          {
            path: 'attendance/imports',
            element: withSuspense(<AttendanceImportsPage />),
          },
          {
            path: 'attendance/holidays',
            element: withSuspense(<AttendanceHolidaysPage />),
          },
          {
            path: 'attendance/status-types',
            element: withSuspense(<AttendanceStatusTypesPage />),
          },
          {
            path: 'attendance/reports',
            element: withSuspense(<AttendanceReportsPage />),
          },
          {
            path: 'attendance/summary',
            element: withSuspense(<AttendanceSummaryPage />),
          },
          {
            path: 'timetable/periods',
            element: withSuspense(<TimetablePeriodsPage />),
          },
          {
            path: 'timetable/rooms',
            element: withSuspense(<TimetableRoomsPage />),
          },
          {
            path: 'timetable/versions',
            element: withSuspense(<TimetableVersionsPage />),
          },
          {
            path: 'timetable/builder',
            element: withSuspense(<WeeklyTimetableBuilderPage />),
          },
          {
            path: 'timetable/class-view',
            element: withSuspense(<ClassTimetableViewPage />),
          },
          {
            path: 'timetable/teacher-view',
            element: withSuspense(<TeacherTimetableViewPage />),
          },
          {
            path: 'timetable/room-view',
            element: withSuspense(<RoomScheduleViewPage />),
          },
          {
            path: 'timetable/conflicts',
            element: withSuspense(<ConflictCheckerPage />),
          },
          {
            path: 'timetable/substitutions',
            element: withSuspense(<SubstitutionsPage />),
          },
          {
            path: 'timetable/exceptions',
            element: withSuspense(<ScheduleExceptionsPage />),
          },
        ],
      },
    ],
  },
]);
