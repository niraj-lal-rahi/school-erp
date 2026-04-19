import { Suspense, lazy } from 'react';
import { createBrowserRouter, Navigate } from 'react-router-dom';
import { FullScreenLoader } from '../components/common/FullScreenLoader';
import { RequireAuth } from '../components/common/RequireAuth';

const AdminLayout = lazy(() => import('../layouts/AdminLayout').then((module) => ({ default: module.AdminLayout })));
const LoginPage = lazy(() => import('../features/auth/LoginPage').then((module) => ({ default: module.LoginPage })));
const StudentListPage = lazy(() => import('../features/students/pages/StudentListPage').then((module) => ({ default: module.StudentListPage })));
const StudentCreatePage = lazy(() => import('../features/students/pages/StudentCreatePage').then((module) => ({ default: module.StudentCreatePage })));
const StudentEditPage = lazy(() => import('../features/students/pages/StudentEditPage').then((module) => ({ default: module.StudentEditPage })));
const StudentProfilePage = lazy(() => import('../features/students/pages/StudentProfilePage').then((module) => ({ default: module.StudentProfilePage })));
const GuardiansPage = lazy(() => import('../features/masterData/pages/GuardiansPage').then((module) => ({ default: module.GuardiansPage })));
const AcademicYearsPage = lazy(() => import('../features/masterData/pages/AcademicYearsPage').then((module) => ({ default: module.AcademicYearsPage })));
const ClassesPage = lazy(() => import('../features/masterData/pages/ClassesPage').then((module) => ({ default: module.ClassesPage })));
const SectionsPage = lazy(() => import('../features/masterData/pages/SectionsPage').then((module) => ({ default: module.SectionsPage })));

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
            element: <Navigate to="/students" replace />,
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
        ],
      },
    ],
  },
]);
