import { configureStore } from '@reduxjs/toolkit';
import authReducer from '../features/auth/authSlice';
import academicManagementReducer from '../features/academicManagement/store/academicManagementSlice';
import masterDataReducer from '../features/masterData/store/masterDataSlice';
import studentsReducer from '../features/students/store/studentSlice';

export const store = configureStore({
  reducer: {
    academicManagement: academicManagementReducer,
    auth: authReducer,
    masterData: masterDataReducer,
    students: studentsReducer,
  },
});
