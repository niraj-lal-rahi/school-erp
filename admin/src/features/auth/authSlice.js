import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { authApi } from './authApi';
import { flattenPermissions } from './authUtils';

const persistedToken = localStorage.getItem('access_token') || '';
const persistedRefreshToken = localStorage.getItem('refresh_token') || '';
const persistedTenantCode = localStorage.getItem('tenant_code') || '';

export const login = createAsyncThunk('auth/login', async (payload, thunkApi) => {
  try {
    const response = await authApi.login(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Login failed.');
  }
});

export const bootstrapSession = createAsyncThunk('auth/bootstrapSession', async (_, thunkApi) => {
  try {
    const response = await authApi.me();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Could not restore session.');
  }
});

export const logout = createAsyncThunk('auth/logout', async (_, thunkApi) => {
  try {
    await authApi.logout();
    return true;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Logout failed.');
  }
});

const initialState = {
  user: null,
  tenantCode: persistedTenantCode,
  accessToken: persistedToken,
  refreshToken: persistedRefreshToken,
  loading: Boolean(persistedToken),
  error: null,
  initialized: false,
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    setSession(state, action) {
      state.user = action.payload.user;
      state.accessToken = action.payload.accessToken;
      state.tenantCode = action.payload.tenantCode;
      state.refreshToken = action.payload.refreshToken || '';
      state.error = null;
      state.loading = false;
      state.initialized = true;
    },
    clearSession(state) {
      state.user = null;
      state.accessToken = '';
      state.tenantCode = '';
      state.refreshToken = '';
      state.loading = false;
      state.initialized = true;
      state.error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(login.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(login.fulfilled, (state, action) => {
        const permissions = flattenPermissions(action.payload.user);
        state.loading = false;
        state.initialized = true;
        state.user = {
          ...action.payload.user,
          permissions,
          role: action.payload.user.roles?.[0]?.slug || 'user',
        };
        state.accessToken = action.payload.access_token;
        state.refreshToken = action.payload.refresh_token;
        state.tenantCode = action.payload.tenant?.code || persistedTenantCode;
      })
      .addCase(login.rejected, (state, action) => {
        state.loading = false;
        state.initialized = true;
        state.error = action.payload;
      })
      .addCase(bootstrapSession.pending, (state) => {
        state.loading = true;
      })
      .addCase(bootstrapSession.fulfilled, (state, action) => {
        const permissions = flattenPermissions(action.payload);
        state.loading = false;
        state.initialized = true;
        state.user = {
          ...action.payload,
          permissions,
          role: action.payload.roles?.[0]?.slug || 'user',
        };
        state.tenantCode = action.payload.school?.code || state.tenantCode;
      })
      .addCase(bootstrapSession.rejected, (state) => {
        state.loading = false;
        state.initialized = true;
        state.user = null;
        state.accessToken = '';
        state.refreshToken = '';
        state.tenantCode = '';
      })
      .addCase(logout.fulfilled, (state) => {
        state.user = null;
        state.accessToken = '';
        state.refreshToken = '';
        state.tenantCode = '';
        state.loading = false;
        state.initialized = true;
      });
  },
});

export const { setSession, clearSession } = authSlice.actions;
export default authSlice.reducer;
