import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { portalApi } from '../services/portalApi';

const STORAGE_KEY = 'portal_active_context';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function loadStoredContext() {
  try {
    const value = localStorage.getItem(STORAGE_KEY);
    return value ? JSON.parse(value) : null;
  } catch (_error) {
    return null;
  }
}

function persistActiveContext(activeContext) {
  if (!activeContext) {
    localStorage.removeItem(STORAGE_KEY);
    return;
  }

  localStorage.setItem(STORAGE_KEY, JSON.stringify(activeContext));
}

function normalizeNotifications(payload) {
  return {
    items: payload?.data || [],
    meta: {
      currentPage: payload?.meta?.current_page || 1,
      lastPage: payload?.meta?.last_page || 1,
      perPage: payload?.meta?.per_page || 15,
      total: payload?.meta?.total || 0,
    },
  };
}

export const fetchPortalContext = createAsyncThunk('portal/fetchContext', async (_, thunkApi) => {
  try {
    const response = await portalApi.getContext();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load portal context.'));
  }
});

export const switchPortalContext = createAsyncThunk('portal/switchContext', async (payload, thunkApi) => {
  try {
    const response = await portalApi.switchContext(payload);
    return response.data.data.context;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to switch portal context.'));
  }
});

export const fetchPortalProfiles = createAsyncThunk('portal/fetchProfiles', async (_, thunkApi) => {
  try {
    const response = await portalApi.getProfiles();
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load portal profiles.'));
  }
});

export const fetchAccessibleStudents = createAsyncThunk('portal/fetchAccessibleStudents', async (_, thunkApi) => {
  try {
    const response = await portalApi.getAccessibleStudents();
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load accessible students.'));
  }
});

export const fetchPortalDashboard = createAsyncThunk('portal/fetchDashboard', async (_, thunkApi) => {
  try {
    const response = await portalApi.getDashboard();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load portal dashboard.'));
  }
});

function createStudentThunk(type, request, fallback) {
  return createAsyncThunk(type, async (studentId, thunkApi) => {
    try {
      const response = await request(studentId);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

export const fetchPortalOverview = createStudentThunk('portal/fetchOverview', portalApi.getOverview, 'Failed to load student overview.');
export const fetchPortalAttendance = createStudentThunk('portal/fetchAttendance', portalApi.getAttendance, 'Failed to load attendance details.');
export const fetchPortalFees = createStudentThunk('portal/fetchFees', portalApi.getFees, 'Failed to load fees data.');
export const fetchPortalResults = createStudentThunk('portal/fetchResults', portalApi.getResults, 'Failed to load results data.');
export const fetchPortalTimetable = createStudentThunk('portal/fetchTimetable', portalApi.getTimetable, 'Failed to load timetable data.');
export const fetchPortalAssignments = createStudentThunk('portal/fetchAssignments', portalApi.getAssignments, 'Failed to load assignments.');
export const fetchPortalTransport = createStudentThunk('portal/fetchTransport', portalApi.getTransport, 'Failed to load transport data.');
export const fetchPortalDocuments = createStudentThunk('portal/fetchDocuments', portalApi.getDocuments, 'Failed to load documents.');

export const fetchPortalNotifications = createAsyncThunk('portal/fetchNotifications', async (params = {}, thunkApi) => {
  try {
    const response = await portalApi.getNotifications(params);
    return normalizeNotifications(response.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load portal notifications.'));
  }
});

export const markPortalNotificationRead = createAsyncThunk('portal/markNotificationRead', async (id, thunkApi) => {
  try {
    const response = await portalApi.markNotificationRead(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark portal notification as read.'));
  }
});

export const markAllPortalNotificationsRead = createAsyncThunk('portal/markAllNotificationsRead', async (_, thunkApi) => {
  try {
    const response = await portalApi.markAllNotificationsRead();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark all portal notifications as read.'));
  }
});

const initialState = {
  context: loadStoredContext() ? { active_context: loadStoredContext() } : null,
  profiles: [],
  accessibleStudents: [],
  dashboard: null,
  overview: null,
  attendance: null,
  fees: null,
  results: null,
  timetable: null,
  assignments: null,
  transport: null,
  documents: null,
  notifications: [],
  notificationsMeta: {
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  },
  loading: false,
  sectionLoading: false,
  switching: false,
  saving: false,
  error: null,
};

const portalSlice = createSlice({
  name: 'portal',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchPortalContext.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPortalContext.fulfilled, (state, action) => {
        state.loading = false;
        state.context = action.payload;
        state.profiles = action.payload.available_profiles || [];
        state.accessibleStudents = action.payload.accessible_students || [];
        persistActiveContext(action.payload.active_context || null);
      })
      .addCase(fetchPortalContext.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(switchPortalContext.pending, (state) => {
        state.switching = true;
        state.error = null;
      })
      .addCase(switchPortalContext.fulfilled, (state, action) => {
        state.switching = false;
        state.context = action.payload;
        state.profiles = action.payload.available_profiles || [];
        state.accessibleStudents = action.payload.accessible_students || [];
        state.dashboard = null;
        state.overview = null;
        state.attendance = null;
        state.fees = null;
        state.results = null;
        state.timetable = null;
        state.assignments = null;
        state.transport = null;
        state.documents = null;
        persistActiveContext(action.payload.active_context || null);
      })
      .addCase(switchPortalContext.rejected, (state, action) => {
        state.switching = false;
        state.error = action.payload;
      })
      .addCase(fetchPortalProfiles.fulfilled, (state, action) => {
        state.profiles = action.payload;
      })
      .addCase(fetchAccessibleStudents.fulfilled, (state, action) => {
        state.accessibleStudents = action.payload;
      })
      .addCase(fetchPortalDashboard.pending, (state) => {
        state.sectionLoading = true;
        state.error = null;
      })
      .addCase(fetchPortalDashboard.fulfilled, (state, action) => {
        state.sectionLoading = false;
        state.dashboard = action.payload;
        state.profiles = action.payload.available_profiles || state.profiles;
        state.accessibleStudents = action.payload.accessible_students || state.accessibleStudents;
        if (action.payload.active_context) {
          persistActiveContext(action.payload.active_context);
        }
      })
      .addCase(fetchPortalDashboard.rejected, (state, action) => {
        state.sectionLoading = false;
        state.error = action.payload;
      });

    [
      [fetchPortalOverview, 'overview'],
      [fetchPortalAttendance, 'attendance'],
      [fetchPortalFees, 'fees'],
      [fetchPortalResults, 'results'],
      [fetchPortalTimetable, 'timetable'],
      [fetchPortalAssignments, 'assignments'],
      [fetchPortalTransport, 'transport'],
      [fetchPortalDocuments, 'documents'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.sectionLoading = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.sectionLoading = false;
          state[key] = action.payload;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.sectionLoading = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(fetchPortalNotifications.pending, (state) => {
        state.sectionLoading = true;
        state.error = null;
      })
      .addCase(fetchPortalNotifications.fulfilled, (state, action) => {
        state.sectionLoading = false;
        state.notifications = action.payload.items;
        state.notificationsMeta = action.payload.meta;
      })
      .addCase(fetchPortalNotifications.rejected, (state, action) => {
        state.sectionLoading = false;
        state.error = action.payload;
      })
      .addCase(markPortalNotificationRead.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(markPortalNotificationRead.fulfilled, (state, action) => {
        state.saving = false;
        state.notifications = state.notifications.map((item) => (
          item.id === action.payload.id ? action.payload : item
        ));
      })
      .addCase(markPortalNotificationRead.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(markAllPortalNotificationsRead.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(markAllPortalNotificationsRead.fulfilled, (state) => {
        state.saving = false;
        state.notifications = state.notifications.map((item) => ({
          ...item,
          is_read: true,
          read_at: item.read_at || new Date().toISOString(),
        }));
      })
      .addCase(markAllPortalNotificationsRead.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default portalSlice.reducer;
