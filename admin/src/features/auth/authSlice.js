import { createSlice } from '@reduxjs/toolkit';

const seededPermissions = [
  'students.view',
  'students.create',
  'students.update',
  'students.delete',
  'students.documents.upload',
];

const initialState = {
  user: {
    id: 1,
    name: 'School Admin',
    email: 'admin@greenwood.edu',
    role: 'school-admin',
    permissions: seededPermissions,
  },
  tenantCode: localStorage.getItem('tenant_code') || 'greenwood',
  accessToken: localStorage.getItem('access_token') || '',
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    setSession(state, action) {
      state.user = action.payload.user;
      state.accessToken = action.payload.accessToken;
      state.tenantCode = action.payload.tenantCode;
    },
    clearSession(state) {
      state.user = null;
      state.accessToken = '';
      state.tenantCode = '';
    },
  },
});

export const { setSession, clearSession } = authSlice.actions;
export default authSlice.reducer;
