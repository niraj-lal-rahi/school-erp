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
const GuardiansPage = lazy(() => import('../features/masterData/pages/GuardiansPage').then((module) => ({ default: module.GuardiansPage })));
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
            path: 'guardians',
            element: withSuspense(<GuardiansPage />),
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
        ],
      },
    ],
  },
]);
