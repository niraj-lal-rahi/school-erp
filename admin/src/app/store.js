import { configureStore } from '@reduxjs/toolkit';
import authReducer from '../features/auth/authSlice';
import masterDataReducer from '../features/masterData/store/masterDataSlice';
import studentsReducer from '../features/students/store/studentSlice';

export const store = configureStore({
  reducer: {
    auth: authReducer,
    masterData: masterDataReducer,
    students: studentsReducer,
  },
});
