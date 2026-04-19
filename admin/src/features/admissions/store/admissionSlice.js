import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { admissionApi } from '../services/admissionApi';

export const fetchAdmissions = createAsyncThunk('admissions/fetchAdmissions', async (params = {}, thunkApi) => {
  try {
    const response = await admissionApi.getAdmissions(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load admissions.');
  }
});

export const fetchAdmissionById = createAsyncThunk('admissions/fetchAdmissionById', async (admissionId, thunkApi) => {
  try {
    const response = await admissionApi.getAdmission(admissionId);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load admission.');
  }
});

export const createAdmission = createAsyncThunk('admissions/createAdmission', async (payload, thunkApi) => {
  try {
    const response = await admissionApi.createAdmission(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create admission.');
  }
});

export const updateAdmission = createAsyncThunk('admissions/updateAdmission', async ({ admissionId, payload }, thunkApi) => {
  try {
    const response = await admissionApi.updateAdmission(admissionId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update admission.');
  }
});

export const runAdmissionAction = createAsyncThunk('admissions/runAction', async ({ action, admissionId, payload }, thunkApi) => {
  try {
    const response = await admissionApi[action](admissionId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update admission workflow.');
  }
});

const initialState = {
  items: [],
  currentAdmission: null,
  filters: {
    search: '',
    application_status: '',
  },
  pagination: {
    page: 1,
    totalPages: 1,
    total: 0,
  },
  loading: false,
  saving: false,
  error: null,
};

const admissionSlice = createSlice({
  name: 'admissions',
  initialState,
  reducers: {
    setAdmissionFilters(state, action) {
      state.filters = {
        ...state.filters,
        ...action.payload,
      };
    },
    setAdmissionPage(state, action) {
      state.pagination.page = action.payload;
    },
    resetCurrentAdmission(state) {
      state.currentAdmission = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchAdmissions.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchAdmissions.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.data || [];
        state.pagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchAdmissions.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchAdmissionById.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchAdmissionById.fulfilled, (state, action) => {
        state.currentAdmission = action.payload;
        state.loading = false;
      })
      .addCase(fetchAdmissionById.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(createAdmission.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createAdmission.fulfilled, (state, action) => {
        state.saving = false;
        state.items.unshift(action.payload);
      })
      .addCase(createAdmission.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(updateAdmission.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(updateAdmission.fulfilled, (state, action) => {
        state.saving = false;
        state.currentAdmission = action.payload;
        state.items = state.items.map((item) => item.id === action.payload.id ? action.payload : item);
      })
      .addCase(updateAdmission.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(runAdmissionAction.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(runAdmissionAction.fulfilled, (state, action) => {
        state.saving = false;
        state.currentAdmission = action.payload;
        state.items = state.items.map((item) => item.id === action.payload.id ? action.payload : item);
      })
      .addCase(runAdmissionAction.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export const { setAdmissionFilters, setAdmissionPage, resetCurrentAdmission } = admissionSlice.actions;
export default admissionSlice.reducer;
