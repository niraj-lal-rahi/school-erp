import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { academicManagementApi } from '../services/academicManagementApi';

function createEmptyResourceState() {
  return {
    items: [],
    current: null,
    loading: false,
    saving: false,
    error: null,
    pagination: {
      page: 1,
      totalPages: 1,
      total: 0,
    },
  };
}

const initialState = {
  options: {
    academicYears: [],
    terms: [],
    classes: [],
    sections: [],
    subjects: [],
    gradingStructures: [],
    staff: [],
  },
  optionsLoading: false,
  resources: {
    academicYears: createEmptyResourceState(),
    terms: createEmptyResourceState(),
    classes: createEmptyResourceState(),
    sections: createEmptyResourceState(),
    subjects: createEmptyResourceState(),
    classSubjects: createEmptyResourceState(),
    teacherAssignments: createEmptyResourceState(),
    curriculum: createEmptyResourceState(),
    lessonPlans: createEmptyResourceState(),
    assignments: createEmptyResourceState(),
    academicCalendar: createEmptyResourceState(),
    gradingStructures: createEmptyResourceState(),
  },
};

export const fetchAcademicOptions = createAsyncThunk('academicManagement/fetchOptions', async (_, thunkApi) => {
  try {
    const response = await academicManagementApi.getOptions();
    const data = response.data.data;
    return {
      academicYears: data.academic_years?.data || [],
      terms: data.terms?.data || [],
      classes: data.classes?.data || [],
      sections: data.sections?.data || [],
      subjects: data.subjects?.data || [],
      gradingStructures: data.grading_structures?.data || [],
      staff: data.staff || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load academic management options.');
  }
});

export const fetchAcademicResource = createAsyncThunk('academicManagement/fetchResource', async ({ resource, params }, thunkApi) => {
  try {
    const response = await academicManagementApi.list(resource, params);
    return { resource, payload: response.data };
  } catch (error) {
    return thunkApi.rejectWithValue({ resource, message: error.response?.data?.message || 'Failed to load records.' });
  }
});

export const createAcademicResource = createAsyncThunk('academicManagement/createResource', async ({ resource, payload }, thunkApi) => {
  try {
    const response = await academicManagementApi.create(resource, payload);
    return { resource, record: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue({ resource, message: error.response?.data?.message || 'Failed to create record.' });
  }
});

export const updateAcademicResource = createAsyncThunk('academicManagement/updateResource', async ({ resource, id, payload }, thunkApi) => {
  try {
    const response = await academicManagementApi.update(resource, id, payload);
    return { resource, record: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue({ resource, message: error.response?.data?.message || 'Failed to update record.' });
  }
});

export const deleteAcademicResource = createAsyncThunk('academicManagement/deleteResource', async ({ resource, id }, thunkApi) => {
  try {
    await academicManagementApi.destroy(resource, id);
    return { resource, id };
  } catch (error) {
    return thunkApi.rejectWithValue({ resource, message: error.response?.data?.message || 'Failed to delete record.' });
  }
});

export const updateAcademicYearStatus = createAsyncThunk('academicManagement/updateAcademicYearStatus', async ({ id, payload }, thunkApi) => {
  try {
    const response = await academicManagementApi.updateAcademicYearStatus(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update academic year status.');
  }
});

const academicManagementSlice = createSlice({
  name: 'academicManagement',
  initialState,
  reducers: {
    resetAcademicCurrent(state, action) {
      state.resources[action.payload].current = null;
      state.resources[action.payload].error = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchAcademicOptions.pending, (state) => {
        state.optionsLoading = true;
      })
      .addCase(fetchAcademicOptions.fulfilled, (state, action) => {
        state.optionsLoading = false;
        state.options = action.payload;
      })
      .addCase(fetchAcademicOptions.rejected, (state) => {
        state.optionsLoading = false;
      })
      .addCase(fetchAcademicResource.pending, (state, action) => {
        state.resources[action.meta.arg.resource].loading = true;
        state.resources[action.meta.arg.resource].error = null;
      })
      .addCase(fetchAcademicResource.fulfilled, (state, action) => {
        const target = state.resources[action.payload.resource];
        target.loading = false;
        target.items = action.payload.payload.data || [];
        target.pagination = {
          page: action.payload.payload.meta?.current_page || 1,
          totalPages: action.payload.payload.meta?.last_page || 1,
          total: action.payload.payload.meta?.total || 0,
        };
      })
      .addCase(fetchAcademicResource.rejected, (state, action) => {
        if (!action.payload) return;
        const target = state.resources[action.payload.resource];
        target.loading = false;
        target.error = action.payload.message;
      })
      .addCase(createAcademicResource.pending, (state, action) => {
        state.resources[action.meta.arg.resource].saving = true;
        state.resources[action.meta.arg.resource].error = null;
      })
      .addCase(createAcademicResource.fulfilled, (state, action) => {
        const target = state.resources[action.payload.resource];
        target.saving = false;
        target.current = action.payload.record;
        target.items = [action.payload.record, ...target.items];
      })
      .addCase(createAcademicResource.rejected, (state, action) => {
        if (!action.payload) return;
        const target = state.resources[action.payload.resource];
        target.saving = false;
        target.error = action.payload.message;
      })
      .addCase(updateAcademicResource.pending, (state, action) => {
        state.resources[action.meta.arg.resource].saving = true;
        state.resources[action.meta.arg.resource].error = null;
      })
      .addCase(updateAcademicResource.fulfilled, (state, action) => {
        const target = state.resources[action.payload.resource];
        target.saving = false;
        target.current = action.payload.record;
        target.items = target.items.map((item) => item.id === action.payload.record.id ? action.payload.record : item);
      })
      .addCase(updateAcademicResource.rejected, (state, action) => {
        if (!action.payload) return;
        const target = state.resources[action.payload.resource];
        target.saving = false;
        target.error = action.payload.message;
      })
      .addCase(deleteAcademicResource.fulfilled, (state, action) => {
        const target = state.resources[action.payload.resource];
        target.items = target.items.filter((item) => item.id !== action.payload.id);
      })
      .addCase(updateAcademicYearStatus.fulfilled, (state, action) => {
        const target = state.resources.academicYears;
        target.items = target.items.map((item) => ({
          ...item,
          is_active: item.id === action.payload.id ? action.payload.is_active : false,
          status: item.id === action.payload.id ? action.payload.status : item.status === 'active' ? 'inactive' : item.status,
        }));
      });
  },
});

export const { resetAcademicCurrent } = academicManagementSlice.actions;
export default academicManagementSlice.reducer;
