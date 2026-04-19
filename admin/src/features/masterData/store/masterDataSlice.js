import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { masterDataApi } from '../services/masterDataApi';

export const fetchMasterData = createAsyncThunk('masterData/fetchMasterData', async (_, thunkApi) => {
  try {
    const [guardians, studentCategories, studentHouses, academicYears, classes, sections] = await Promise.all([
      masterDataApi.getGuardians(),
      masterDataApi.getStudentCategories(),
      masterDataApi.getStudentHouses(),
      masterDataApi.getAcademicYears(),
      masterDataApi.getClasses(),
      masterDataApi.getSections(),
    ]);

    return {
      guardians: guardians.data.data || [],
      studentCategories: studentCategories.data.data || [],
      studentHouses: studentHouses.data.data || [],
      academicYears: academicYears.data.data || [],
      classes: classes.data.data || [],
      sections: sections.data.data || [],
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

export const updateGuardian = createAsyncThunk('masterData/updateGuardian', async ({ guardianId, payload }, thunkApi) => {
  try {
    const response = await masterDataApi.updateGuardian(guardianId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update guardian.');
  }
});

export const deleteGuardian = createAsyncThunk('masterData/deleteGuardian', async (guardianId, thunkApi) => {
  try {
    await masterDataApi.deleteGuardian(guardianId);
    return guardianId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete guardian.');
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

export const createStudentCategory = createAsyncThunk('masterData/createStudentCategory', async (payload, thunkApi) => {
  try {
    const response = await masterDataApi.createStudentCategory(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create student category.');
  }
});

export const updateStudentCategory = createAsyncThunk('masterData/updateStudentCategory', async ({ categoryId, payload }, thunkApi) => {
  try {
    const response = await masterDataApi.updateStudentCategory(categoryId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update student category.');
  }
});

export const deleteStudentCategory = createAsyncThunk('masterData/deleteStudentCategory', async (categoryId, thunkApi) => {
  try {
    await masterDataApi.deleteStudentCategory(categoryId);
    return categoryId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete student category.');
  }
});

export const createStudentHouse = createAsyncThunk('masterData/createStudentHouse', async (payload, thunkApi) => {
  try {
    const response = await masterDataApi.createStudentHouse(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create student house.');
  }
});

export const updateStudentHouse = createAsyncThunk('masterData/updateStudentHouse', async ({ houseId, payload }, thunkApi) => {
  try {
    const response = await masterDataApi.updateStudentHouse(houseId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update student house.');
  }
});

export const deleteStudentHouse = createAsyncThunk('masterData/deleteStudentHouse', async (houseId, thunkApi) => {
  try {
    await masterDataApi.deleteStudentHouse(houseId);
    return houseId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete student house.');
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

export const createSection = createAsyncThunk('masterData/createSection', async (payload, thunkApi) => {
  try {
    const response = await masterDataApi.createSection(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to create section.');
  }
});

export const updateSection = createAsyncThunk('masterData/updateSection', async ({ sectionId, payload }, thunkApi) => {
  try {
    const response = await masterDataApi.updateSection(sectionId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to update section.');
  }
});

export const deleteSection = createAsyncThunk('masterData/deleteSection', async (sectionId, thunkApi) => {
  try {
    await masterDataApi.deleteSection(sectionId);
    return sectionId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete section.');
  }
});

const initialState = {
  guardians: [],
  studentCategories: [],
  studentHouses: [],
  academicYears: [],
  classes: [],
  sections: [],
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
        state.studentCategories = action.payload.studentCategories;
        state.studentHouses = action.payload.studentHouses;
        state.academicYears = action.payload.academicYears;
        state.classes = action.payload.classes;
        state.sections = action.payload.sections;
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
      .addCase(updateGuardian.fulfilled, (state, action) => {
        state.saving = false;
        state.guardians = state.guardians.map((guardian) => guardian.id === action.payload.id ? action.payload : guardian);
      })
      .addCase(deleteGuardian.fulfilled, (state, action) => {
        state.saving = false;
        state.guardians = state.guardians.filter((guardian) => guardian.id !== action.payload);
      })
      .addCase(createStudentCategory.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createStudentCategory.fulfilled, (state, action) => {
        state.saving = false;
        state.studentCategories.unshift(action.payload);
      })
      .addCase(createStudentCategory.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(updateStudentCategory.fulfilled, (state, action) => {
        state.saving = false;
        state.studentCategories = state.studentCategories.map((category) => category.id === action.payload.id ? action.payload : category);
      })
      .addCase(deleteStudentCategory.fulfilled, (state, action) => {
        state.saving = false;
        state.studentCategories = state.studentCategories.filter((category) => category.id !== action.payload);
      })
      .addCase(createStudentHouse.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createStudentHouse.fulfilled, (state, action) => {
        state.saving = false;
        state.studentHouses.unshift(action.payload);
      })
      .addCase(createStudentHouse.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(updateStudentHouse.fulfilled, (state, action) => {
        state.saving = false;
        state.studentHouses = state.studentHouses.map((house) => house.id === action.payload.id ? action.payload : house);
      })
      .addCase(deleteStudentHouse.fulfilled, (state, action) => {
        state.saving = false;
        state.studentHouses = state.studentHouses.filter((house) => house.id !== action.payload);
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
      })
      .addCase(createSection.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createSection.fulfilled, (state, action) => {
        state.saving = false;
        state.sections.push(action.payload);

        state.classes = state.classes.map((schoolClass) => {
          if (schoolClass.id !== action.payload.school_class_id) {
            return schoolClass;
          }

          return {
            ...schoolClass,
            sections: [...(schoolClass.sections || []), action.payload],
          };
        });
      })
      .addCase(createSection.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(updateSection.fulfilled, (state, action) => {
        state.saving = false;
        state.sections = state.sections.map((section) => section.id === action.payload.id ? action.payload : section);
        state.classes = state.classes.map((schoolClass) => {
          if (schoolClass.id !== action.payload.school_class_id) {
            return {
              ...schoolClass,
              sections: (schoolClass.sections || []).filter((section) => section.id !== action.payload.id),
            };
          }

          const nextSections = (schoolClass.sections || []).filter((section) => section.id !== action.payload.id);
          return { ...schoolClass, sections: [...nextSections, action.payload] };
        });
      })
      .addCase(deleteSection.fulfilled, (state, action) => {
        state.saving = false;
        state.sections = state.sections.filter((section) => section.id !== action.payload);
        state.classes = state.classes.map((schoolClass) => ({
          ...schoolClass,
          sections: (schoolClass.sections || []).filter((section) => section.id !== action.payload),
        }));
      });
  },
});

export default masterDataSlice.reducer;
