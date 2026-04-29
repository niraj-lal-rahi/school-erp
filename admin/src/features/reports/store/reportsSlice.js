import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { reportsApi } from '../services/reportsApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function normalizePaginated(payload) {
  return {
    items: payload?.data || [],
    pagination: {
      page: payload?.current_page || 1,
      totalPages: payload?.last_page || 1,
      total: payload?.total || 0,
    },
  };
}

function upsertItem(items, payload) {
  const exists = items.some((item) => item.id === payload.id);
  if (!exists) {
    return [payload, ...items];
  }

  return items.map((item) => (item.id === payload.id ? payload : item));
}

function createMutationThunk(type, request, fallback) {
  return createAsyncThunk(type, async (payload, thunkApi) => {
    try {
      const response = await request(payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

function createUpdateThunk(type, request, fallback) {
  return createAsyncThunk(type, async ({ id, payload }, thunkApi) => {
    try {
      const response = await request(id, payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

function createDeleteThunk(type, request, fallback) {
  return createAsyncThunk(type, async (id, thunkApi) => {
    try {
      await request(id);
      return id;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

export const fetchReportsReferenceData = createAsyncThunk(
  'reports/fetchReferenceData',
  async (_, thunkApi) => {
    try {
      const [academicYears, classes, sections] = await Promise.all([
        reportsApi.getAcademicYears(),
        reportsApi.getClasses(),
        reportsApi.getSections(),
      ]);

      return {
        academicYears: academicYears.data.data || [],
        classes: classes.data.data || [],
        sections: sections.data.data || [],
      };
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, 'Failed to load report reference data.'));
    }
  },
);

export const fetchReportsDashboard = createAsyncThunk('reports/fetchDashboard', async (params = {}, thunkApi) => {
  try {
    const response = await reportsApi.getDashboard(params);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load dashboard analytics.'));
  }
});

export const fetchReportWidgets = createAsyncThunk('reports/fetchWidgets', async (params = {}, thunkApi) => {
  try {
    const response = await reportsApi.getWidgets(params);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load dashboard widgets.'));
  }
});

export const updateDashboardLayout = createMutationThunk('reports/updateLayout', reportsApi.updateLayout, 'Failed to update dashboard layout.');

export const fetchReportDefinitions = createAsyncThunk('reports/fetchDefinitions', async (params = {}, thunkApi) => {
  try {
    const response = await reportsApi.getDefinitions(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load report definitions.'));
  }
});

export const createReportDefinition = createMutationThunk('reports/createDefinition', reportsApi.createDefinition, 'Failed to create report definition.');
export const updateReportDefinition = createUpdateThunk('reports/updateDefinition', reportsApi.updateDefinition, 'Failed to update report definition.');
export const deleteReportDefinition = createDeleteThunk('reports/deleteDefinition', reportsApi.deleteDefinition, 'Failed to delete report definition.');

export const fetchReportRuns = createAsyncThunk('reports/fetchRuns', async (params = {}, thunkApi) => {
  try {
    const response = await reportsApi.getRuns(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load report runs.'));
  }
});

export const fetchReportRun = createAsyncThunk('reports/fetchRun', async (id, thunkApi) => {
  try {
    const response = await reportsApi.getRun(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load report run details.'));
  }
});

export const runReport = createMutationThunk('reports/runReport', reportsApi.runReport, 'Failed to run report.');

export const fetchReportSchedules = createAsyncThunk('reports/fetchSchedules', async (params = {}, thunkApi) => {
  try {
    const response = await reportsApi.getSchedules(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load report schedules.'));
  }
});

export const createReportSchedule = createMutationThunk('reports/createSchedule', reportsApi.createSchedule, 'Failed to create report schedule.');
export const updateReportSchedule = createUpdateThunk('reports/updateSchedule', reportsApi.updateSchedule, 'Failed to update report schedule.');
export const pauseReportSchedule = createAsyncThunk('reports/pauseSchedule', async (id, thunkApi) => {
  try {
    const response = await reportsApi.pauseSchedule(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to pause report schedule.'));
  }
});
export const resumeReportSchedule = createAsyncThunk('reports/resumeSchedule', async ({ id, payload }, thunkApi) => {
  try {
    const response = await reportsApi.resumeSchedule(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to resume report schedule.'));
  }
});

const initialState = {
  dashboard: null,
  widgets: [],
  layout: [],
  definitions: [],
  runs: [],
  schedules: [],
  selectedRun: null,
  referenceData: {
    academicYears: [],
    classes: [],
    sections: [],
  },
  definitionsPagination: { page: 1, totalPages: 1, total: 0 },
  runsPagination: { page: 1, totalPages: 1, total: 0 },
  schedulesPagination: { page: 1, totalPages: 1, total: 0 },
  loading: false,
  saving: false,
  error: null,
};

const reportsSlice = createSlice({
  name: 'reports',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchReportsReferenceData.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchReportsReferenceData.fulfilled, (state, action) => {
        state.loading = false;
        state.referenceData = action.payload;
      })
      .addCase(fetchReportsReferenceData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchReportsDashboard.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchReportsDashboard.fulfilled, (state, action) => {
        state.loading = false;
        state.dashboard = action.payload;
      })
      .addCase(fetchReportsDashboard.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchReportWidgets.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchReportWidgets.fulfilled, (state, action) => {
        state.loading = false;
        state.widgets = action.payload.widgets || [];
        state.layout = action.payload.layout || [];
      })
      .addCase(fetchReportWidgets.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchReportRun.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchReportRun.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedRun = action.payload;
        state.runs = upsertItem(state.runs, action.payload);
      })
      .addCase(fetchReportRun.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [fetchReportDefinitions, 'definitions', 'definitionsPagination'],
      [fetchReportRuns, 'runs', 'runsPagination'],
      [fetchReportSchedules, 'schedules', 'schedulesPagination'],
    ].forEach(([thunk, key, paginationKey]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.loading = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.loading = false;
          state[key] = action.payload.items;
          state[paginationKey] = action.payload.pagination;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.loading = false;
          state.error = action.payload;
        });
    });

    [
      [createReportDefinition, 'definitions'],
      [updateReportDefinition, 'definitions'],
      [runReport, 'runs'],
      [createReportSchedule, 'schedules'],
      [updateReportSchedule, 'schedules'],
      [pauseReportSchedule, 'schedules'],
      [resumeReportSchedule, 'schedules'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = upsertItem(state[key], action.payload);
          if (key === 'runs') {
            state.selectedRun = action.payload;
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(updateDashboardLayout.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(updateDashboardLayout.fulfilled, (state, action) => {
        state.saving = false;
        state.layout = action.payload.layout || [];
      })
      .addCase(updateDashboardLayout.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(deleteReportDefinition.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(deleteReportDefinition.fulfilled, (state, action) => {
        state.saving = false;
        state.definitions = state.definitions.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteReportDefinition.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default reportsSlice.reducer;
