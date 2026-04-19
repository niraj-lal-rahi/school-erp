import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { enrollmentApi } from '../services/enrollmentApi';

export const fetchEnrollments = createAsyncThunk('enrollments/fetchEnrollments', async (params = {}, thunkApi) => {
  try {
    const response = await enrollmentApi.getEnrollments(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load enrollments.');
  }
});

export const createEnrollment = createAsyncThunk('enrollments/createEnrollment', async (payload, thunkApi) => {
  try {
    const response = await enrollmentApi.createEnrollment(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create enrollment.');
  }
});

export const updateEnrollment = createAsyncThunk('enrollments/updateEnrollment', async ({ enrollmentId, payload }, thunkApi) => {
  try {
    const response = await enrollmentApi.updateEnrollment(enrollmentId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update enrollment.');
  }
});

const initialState = {
  items: [],
  filters: {
    search: '',
    status: '',
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

const enrollmentSlice = createSlice({
  name: 'enrollments',
  initialState,
  reducers: {
    setEnrollmentFilters(state, action) {
      state.filters = { ...state.filters, ...action.payload };
    },
    setEnrollmentPage(state, action) {
      state.pagination.page = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchEnrollments.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchEnrollments.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.data || [];
        state.pagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchEnrollments.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(createEnrollment.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createEnrollment.fulfilled, (state, action) => {
        state.saving = false;
        state.items.unshift(action.payload);
      })
      .addCase(createEnrollment.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(updateEnrollment.fulfilled, (state, action) => {
        state.saving = false;
        state.items = state.items.map((item) => item.id === action.payload.id ? action.payload : item);
      });
  },
});

export const { setEnrollmentFilters, setEnrollmentPage } = enrollmentSlice.actions;
export default enrollmentSlice.reducer;
