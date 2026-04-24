import { configureStore } from '@reduxjs/toolkit';
import admissionReducer from '../features/admissions/store/admissionSlice';
import authReducer from '../features/auth/authSlice';
import academicManagementReducer from '../features/academicManagement/store/academicManagementSlice';
import enrollmentReducer from '../features/enrollments/store/enrollmentSlice';
import financeReducer from '../features/finance/store/financeSlice';
import hrReducer from '../features/hr/store/hrSlice';
import masterDataReducer from '../features/masterData/store/masterDataSlice';
import studentsReducer from '../features/students/store/studentSlice';

export const store = configureStore({
  reducer: {
    admissions: admissionReducer,
    academicManagement: academicManagementReducer,
    auth: authReducer,
    enrollments: enrollmentReducer,
    finance: financeReducer,
    hr: hrReducer,
    masterData: masterDataReducer,
    students: studentsReducer,
  },
});
