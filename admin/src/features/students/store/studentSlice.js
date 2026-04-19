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

export const saveStudentMedical = createAsyncThunk(
  'students/saveStudentMedical',
  async ({ studentId, payload }, thunkApi) => {
    try {
      const response = await studentApi.saveStudentMedical(studentId, payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to save medical record.');
    }
  }
);

export const deleteStudentDocument = createAsyncThunk(
  'students/deleteStudentDocument',
  async (documentId, thunkApi) => {
    try {
      await studentApi.deleteStudentDocument(documentId);
      return documentId;
    } catch (error) {
      return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete student document.');
    }
  }
);

export const addStudentNote = createAsyncThunk(
  'students/addStudentNote',
  async ({ studentId, payload }, thunkApi) => {
    try {
      const response = await studentApi.addStudentNote(studentId, payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to save student note.');
    }
  }
);

export const deleteStudentNote = createAsyncThunk(
  'students/deleteStudentNote',
  async (noteId, thunkApi) => {
    try {
      await studentApi.deleteStudentNote(noteId);
      return noteId;
    } catch (error) {
      return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete student note.');
    }
  }
);

function lifecycleThunk(type, request) {
  return createAsyncThunk(type, async ({ studentId, payload }, thunkApi) => {
    try {
      const response = await request(studentId, payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update student lifecycle.');
    }
  });
}

export const promoteStudent = lifecycleThunk('students/promoteStudent', studentApi.promoteStudent);
export const transferStudentSection = lifecycleThunk('students/transferStudentSection', studentApi.transferStudentSection);
export const withdrawStudent = lifecycleThunk('students/withdrawStudent', studentApi.withdrawStudent);
export const graduateStudent = lifecycleThunk('students/graduateStudent', studentApi.graduateStudent);
export const suspendStudent = lifecycleThunk('students/suspendStudent', studentApi.suspendStudent);
export const reactivateStudent = lifecycleThunk('students/reactivateStudent', studentApi.reactivateStudent);

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
    academic_year_id: '',
    school_class_id: '',
    section_id: '',
    category_id: '',
    house_id: '',
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
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
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
        state.error = null;
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
      })
      .addCase(saveStudentMedical.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(saveStudentMedical.fulfilled, (state, action) => {
        state.saving = false;
        if (state.currentStudent) {
          state.currentStudent.medical_record = action.payload;
        }
      })
      .addCase(saveStudentMedical.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(deleteStudentDocument.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(deleteStudentDocument.fulfilled, (state, action) => {
        state.saving = false;
        if (state.currentStudent) {
          state.currentStudent.documents = (state.currentStudent.documents || []).filter((document) => document.id !== action.payload);
        }
      })
      .addCase(deleteStudentDocument.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(addStudentNote.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(addStudentNote.fulfilled, (state, action) => {
        state.saving = false;
        if (state.currentStudent) {
          state.currentStudent.notes_entries = [action.payload, ...(state.currentStudent.notes_entries || [])];
        }
      })
      .addCase(addStudentNote.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(deleteStudentNote.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(deleteStudentNote.fulfilled, (state, action) => {
        state.saving = false;
        if (state.currentStudent) {
          state.currentStudent.notes_entries = (state.currentStudent.notes_entries || []).filter((note) => note.id !== action.payload);
        }
      })
      .addCase(deleteStudentNote.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });

    [
      promoteStudent,
      transferStudentSection,
      withdrawStudent,
      graduateStudent,
      suspendStudent,
      reactivateStudent,
    ].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state.currentStudent = action.payload;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });
  },
});

export const { setStudentFilters, setStudentPage, resetCurrentStudent } = studentSlice.actions;
export default studentSlice.reducer;
