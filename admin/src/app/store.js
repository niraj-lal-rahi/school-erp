import { configureStore } from '@reduxjs/toolkit';
import admissionReducer from '../features/admissions/store/admissionSlice';
import attendanceReducer from '../features/attendance/store/attendanceSlice';
import authReducer from '../features/auth/authSlice';
import academicManagementReducer from '../features/academicManagement/store/academicManagementSlice';
import communicationReducer from '../features/communication/store/communicationSlice';
import enrollmentReducer from '../features/enrollments/store/enrollmentSlice';
import examinationReducer from '../features/examination/store/examinationSlice';
import financeReducer from '../features/finance/store/financeSlice';
import hrReducer from '../features/hr/store/hrSlice';
import masterDataReducer from '../features/masterData/store/masterDataSlice';
import portalReducer from '../features/portal/store/portalSlice';
import rbacReducer from '../features/rbac/store/rbacSlice';
import reportsReducer from '../features/reports/store/reportsSlice';
import saasReducer from '../features/saas/store/saasSlice';
import studentsReducer from '../features/students/store/studentSlice';
import timetableReducer from '../features/timetable/store/timetableSlice';
import transportReducer from '../features/transport/store/transportSlice';

export const store = configureStore({
  reducer: {
    admissions: admissionReducer,
    academicManagement: academicManagementReducer,
    attendance: attendanceReducer,
    auth: authReducer,
    enrollments: enrollmentReducer,
    communication: communicationReducer,
    finance: financeReducer,
    hr: hrReducer,
    examination: examinationReducer,
    masterData: masterDataReducer,
    portal: portalReducer,
    rbac: rbacReducer,
    reports: reportsReducer,
    saas: saasReducer,
    students: studentsReducer,
    timetable: timetableReducer,
    transport: transportReducer,
  },
});
