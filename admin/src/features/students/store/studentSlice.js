import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { studentApi } from '../services/studentApi';

export const fetchStudents = createAsyncThunk('students/fetchStudents', async (params = {}, thunkApi) => {
  try {
    const response = await studentApi.getStudents(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load students.');
  }
});

export const fetchStudentById = createAsyncThunk('students/fetchStudentById', async (studentId, thunkApi) => {
  try {
    const response = await studentApi.getStudent(studentId);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load student profile.');
  }
});

export const createStudent = createAsyncThunk('students/createStudent', async (payload, thunkApi) => {
  try {
    const response = await studentApi.createStudent(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create student.');
  }
});

export const updateStudent = createAsyncThunk('students/updateStudent', async ({ studentId, payload }, thunkApi) => {
  try {
    const response = await studentApi.updateStudent(studentId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update student.');
  }
});

export const uploadStudentDocument = createAsyncThunk(
  'students/uploadStudentDocument',
  async ({ studentId, payload }, thunkApi) => {
    try {
      const response = await studentApi.uploadStudentDocument(studentId, payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to upload document.');
    }
  }
);

const initialState = {
  items: [],
  currentStudent: null,
  pagination: {
    page: 1,
    totalPages: 1,
    total: 0,
  },
  filters: {
    search: '',
    status: '',
  },
  loading: false,
  saving: false,
  error: null,
};

const studentSlice = createSlice({
  name: 'students',
  initialState,
  reducers: {
    setStudentFilters(state, action) {
      state.filters = {
        ...state.filters,
        ...action.payload,
      };
    },
    setStudentPage(state, action) {
      state.pagination.page = action.payload;
    },
    resetCurrentStudent(state) {
      state.currentStudent = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchStudents.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchStudents.fulfilled, (state, action) => {
        state.loading = false;
        state.items = action.payload.data || [];
        state.pagination = {
          page: action.payload.current_page || 1,
          totalPages: action.payload.last_page || 1,
          total: action.payload.total || 0,
        };
      })
      .addCase(fetchStudents.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchStudentById.pending, (state) => {
        state.loading = true;
      })
      .addCase(fetchStudentById.fulfilled, (state, action) => {
        state.loading = false;
        state.currentStudent = action.payload;
      })
      .addCase(fetchStudentById.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(createStudent.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createStudent.fulfilled, (state, action) => {
        state.saving = false;
        state.currentStudent = action.payload;
      })
      .addCase(createStudent.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(updateStudent.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(updateStudent.fulfilled, (state, action) => {
        state.saving = false;
        state.currentStudent = action.payload;
      })
      .addCase(updateStudent.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(uploadStudentDocument.pending, (state) => {
        state.saving = true;
      })
      .addCase(uploadStudentDocument.fulfilled, (state, action) => {
        state.saving = false;
        if (state.currentStudent) {
          state.currentStudent.documents = [...(state.currentStudent.documents || []), action.payload];
        }
      })
      .addCase(uploadStudentDocument.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export const { setStudentFilters, setStudentPage, resetCurrentStudent } = studentSlice.actions;
export default studentSlice.reducer;
