import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { masterDataApi } from '../services/masterDataApi';

export const fetchMasterData = createAsyncThunk('masterData/fetchMasterData', async (_, thunkApi) => {
  try {
    const [guardians, academicYears, classes] = await Promise.all([
      masterDataApi.getGuardians(),
      masterDataApi.getAcademicYears(),
      masterDataApi.getClasses(),
    ]);

    return {
      guardians: guardians.data.data || [],
      academicYears: academicYears.data.data || [],
      classes: classes.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load master data.');
  }
});

export const createGuardian = createAsyncThunk('masterData/createGuardian', async (payload, thunkApi) => {
  try {
    const response = await masterDataApi.createGuardian(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create guardian.');
  }
});

export const createAcademicYear = createAsyncThunk('masterData/createAcademicYear', async (payload, thunkApi) => {
  try {
    const response = await masterDataApi.createAcademicYear(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create academic year.');
  }
});

export const createSchoolClass = createAsyncThunk('masterData/createSchoolClass', async (payload, thunkApi) => {
  try {
    const response = await masterDataApi.createClass(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create class.');
  }
});

const initialState = {
  guardians: [],
  academicYears: [],
  classes: [],
  loading: false,
  saving: false,
  error: null,
};

const masterDataSlice = createSlice({
  name: 'masterData',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchMasterData.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchMasterData.fulfilled, (state, action) => {
        state.loading = false;
        state.guardians = action.payload.guardians;
        state.academicYears = action.payload.academicYears;
        state.classes = action.payload.classes;
      })
      .addCase(fetchMasterData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(createGuardian.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createGuardian.fulfilled, (state, action) => {
        state.saving = false;
        state.guardians.unshift(action.payload);
      })
      .addCase(createGuardian.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(createAcademicYear.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createAcademicYear.fulfilled, (state, action) => {
        state.saving = false;
        if (action.payload.is_current) {
          state.academicYears = state.academicYears.map((item) => ({ ...item, is_current: false }));
        }
        state.academicYears.unshift(action.payload);
      })
      .addCase(createAcademicYear.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(createSchoolClass.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createSchoolClass.fulfilled, (state, action) => {
        state.saving = false;
        state.classes.push(action.payload);
      })
      .addCase(createSchoolClass.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default masterDataSlice.reducer;
