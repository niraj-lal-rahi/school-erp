import { createBrowserRouter, Navigate } from 'react-router-dom';
import { AdminLayout } from '../layouts/AdminLayout';
import { StudentCreatePage } from '../features/students/pages/StudentCreatePage';
import { StudentEditPage } from '../features/students/pages/StudentEditPage';
import { StudentListPage } from '../features/students/pages/StudentListPage';
import { StudentProfilePage } from '../features/students/pages/StudentProfilePage';

export const router = createBrowserRouter([
  {
    path: '/',
    element: <AdminLayout />,
    children: [
      {
        index: true,
        element: <Navigate to="/students" replace />,
      },
      {
        path: 'students',
        element: <StudentListPage />,
      },
      {
        path: 'students/new',
        element: <StudentCreatePage />,
      },
      {
        path: 'students/:studentId',
        element: <StudentProfilePage />,
      },
      {
        path: 'students/:studentId/edit',
        element: <StudentEditPage />,
      },
    ],
  },
]);
