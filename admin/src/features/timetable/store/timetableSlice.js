import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { timetableApi } from '../services/timetableApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

export const fetchTimetableOptions = createAsyncThunk('timetable/fetchOptions', async (_, thunkApi) => {
  try {
    const response = await timetableApi.getOptions();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load timetable options.'));
  }
});

export const fetchTimetablePeriods = createAsyncThunk('timetable/fetchPeriods', async (params = {}, thunkApi) => {
  try {
    const response = await timetableApi.getPeriods(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load timetable periods.'));
  }
});

export const createTimetablePeriod = createAsyncThunk('timetable/createPeriod', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.createPeriod(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create timetable period.'));
  }
});

export const updateTimetablePeriod = createAsyncThunk('timetable/updatePeriod', async ({ id, payload }, thunkApi) => {
  try {
    const response = await timetableApi.updatePeriod(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update timetable period.'));
  }
});

export const deleteTimetablePeriod = createAsyncThunk('timetable/deletePeriod', async (id, thunkApi) => {
  try {
    await timetableApi.deletePeriod(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete timetable period.'));
  }
});

export const fetchTimetableRooms = createAsyncThunk('timetable/fetchRooms', async (params = {}, thunkApi) => {
  try {
    const response = await timetableApi.getRooms(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load timetable rooms.'));
  }
});

export const createTimetableRoom = createAsyncThunk('timetable/createRoom', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.createRoom(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create timetable room.'));
  }
});

export const updateTimetableRoom = createAsyncThunk('timetable/updateRoom', async ({ id, payload }, thunkApi) => {
  try {
    const response = await timetableApi.updateRoom(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update timetable room.'));
  }
});

export const deleteTimetableRoom = createAsyncThunk('timetable/deleteRoom', async (id, thunkApi) => {
  try {
    await timetableApi.deleteRoom(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete timetable room.'));
  }
});

export const fetchTimetableVersions = createAsyncThunk('timetable/fetchVersions', async (params = {}, thunkApi) => {
  try {
    const response = await timetableApi.getVersions(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load timetable versions.'));
  }
});

export const createTimetableVersion = createAsyncThunk('timetable/createVersion', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.createVersion(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create timetable version.'));
  }
});

export const updateTimetableVersion = createAsyncThunk('timetable/updateVersion', async ({ id, payload }, thunkApi) => {
  try {
    const response = await timetableApi.updateVersion(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update timetable version.'));
  }
});

export const deleteTimetableVersion = createAsyncThunk('timetable/deleteVersion', async (id, thunkApi) => {
  try {
    await timetableApi.deleteVersion(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete timetable version.'));
  }
});

export const publishTimetableVersion = createAsyncThunk('timetable/publishVersion', async ({ id, payload = {} }, thunkApi) => {
  try {
    const response = await timetableApi.publishVersion(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to publish timetable version.'));
  }
});

export const archiveTimetableVersion = createAsyncThunk('timetable/archiveVersion', async ({ id, payload = {} }, thunkApi) => {
  try {
    const response = await timetableApi.archiveVersion(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to archive timetable version.'));
  }
});

export const duplicateTimetableVersion = createAsyncThunk('timetable/duplicateVersion', async (id, thunkApi) => {
  try {
    const response = await timetableApi.duplicateVersion(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to duplicate timetable version.'));
  }
});

export const fetchTimetableEntries = createAsyncThunk('timetable/fetchEntries', async (params = {}, thunkApi) => {
  try {
    const response = await timetableApi.getEntries(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load timetable entries.'));
  }
});

export const fetchTimetableSubstitutions = createAsyncThunk('timetable/fetchSubstitutions', async (params = {}, thunkApi) => {
  try {
    const response = await timetableApi.getSubstitutions(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load timetable substitutions.'));
  }
});

export const createTimetableSubstitution = createAsyncThunk('timetable/createSubstitution', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.createSubstitution(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create timetable substitution.'));
  }
});

export const updateTimetableSubstitution = createAsyncThunk('timetable/updateSubstitution', async ({ id, payload }, thunkApi) => {
  try {
    const response = await timetableApi.updateSubstitution(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update timetable substitution.'));
  }
});

export const deleteTimetableSubstitution = createAsyncThunk('timetable/deleteSubstitution', async (id, thunkApi) => {
  try {
    await timetableApi.deleteSubstitution(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete timetable substitution.'));
  }
});

export const approveTimetableSubstitution = createAsyncThunk('timetable/approveSubstitution', async (id, thunkApi) => {
  try {
    const response = await timetableApi.approveSubstitution(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to approve timetable substitution.'));
  }
});

export const cancelTimetableSubstitution = createAsyncThunk('timetable/cancelSubstitution', async (id, thunkApi) => {
  try {
    const response = await timetableApi.cancelSubstitution(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to cancel timetable substitution.'));
  }
});

export const fetchTimetableExceptions = createAsyncThunk('timetable/fetchExceptions', async (params = {}, thunkApi) => {
  try {
    const response = await timetableApi.getExceptions(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load schedule exceptions.'));
  }
});

export const createTimetableException = createAsyncThunk('timetable/createException', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.createException(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create schedule exception.'));
  }
});

export const updateTimetableException = createAsyncThunk('timetable/updateException', async ({ id, payload }, thunkApi) => {
  try {
    const response = await timetableApi.updateException(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update schedule exception.'));
  }
});

export const deleteTimetableException = createAsyncThunk('timetable/deleteException', async (id, thunkApi) => {
  try {
    await timetableApi.deleteException(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete schedule exception.'));
  }
});

export const createTimetableEntry = createAsyncThunk('timetable/createEntry', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.createEntry(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create timetable entry.'));
  }
});

export const updateTimetableEntry = createAsyncThunk('timetable/updateEntry', async ({ id, payload }, thunkApi) => {
  try {
    const response = await timetableApi.updateEntry(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update timetable entry.'));
  }
});

export const deleteTimetableEntry = createAsyncThunk('timetable/deleteEntry', async (id, thunkApi) => {
  try {
    await timetableApi.deleteEntry(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete timetable entry.'));
  }
});

export const bulkCreateTimetableEntries = createAsyncThunk('timetable/bulkCreateEntries', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.bulkCreateEntries(payload);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create timetable entries in bulk.'));
  }
});

export const checkTimetableConflicts = createAsyncThunk('timetable/checkConflicts', async (payload, thunkApi) => {
  try {
    const response = await timetableApi.checkConflicts(payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to check timetable conflicts.'));
  }
});

export const fetchClassWeeklyTimetable = createAsyncThunk('timetable/fetchClassWeekly', async ({ classId, sectionId, params = {} }, thunkApi) => {
  try {
    const response = await timetableApi.getClassWeekly(classId, sectionId, params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load class weekly timetable.'));
  }
});

export const fetchStaffWeeklyTimetable = createAsyncThunk('timetable/fetchStaffWeekly', async ({ staffId, params = {} }, thunkApi) => {
  try {
    const response = await timetableApi.getStaffWeekly(staffId, params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load staff weekly timetable.'));
  }
});

export const fetchStaffDailyTimetable = createAsyncThunk('timetable/fetchStaffDaily', async ({ staffId, params = {} }, thunkApi) => {
  try {
    const response = await timetableApi.getStaffDaily(staffId, params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load staff daily timetable.'));
  }
});

function upsertItem(items, payload) {
  const existing = items.some((item) => item.id === payload.id);
  if (!existing) {
    return [payload, ...items];
  }

  return items.map((item) => (item.id === payload.id ? payload : item));
}

const initialState = {
  periods: [],
  rooms: [],
  versions: [],
  entries: [],
  substitutions: [],
  exceptions: [],
  classWeekly: [],
  staffWeekly: [],
  staffDaily: [],
  conflictCheck: {
    hasConflicts: false,
    conflicts: [],
  },
  options: {
    academicYears: [],
    periods: [],
    rooms: [],
    versions: [],
    classes: [],
    sections: [],
    staff: [],
    subjects: [],
  },
  loading: false,
  saving: false,
  error: null,
};

const timetableSlice = createSlice({
  name: 'timetable',
  initialState,
  reducers: {
    clearTimetableConflictCheck(state) {
      state.conflictCheck = {
        hasConflicts: false,
        conflicts: [],
      };
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchTimetableOptions.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTimetableOptions.fulfilled, (state, action) => {
        state.loading = false;
        state.options = {
          academicYears: action.payload.academic_years || [],
          periods: action.payload.periods || [],
          rooms: action.payload.rooms || [],
          versions: action.payload.versions || [],
          classes: action.payload.classes || [],
          sections: action.payload.sections || [],
          staff: action.payload.staff || [],
          subjects: action.payload.subjects || [],
        };
      })
      .addCase(fetchTimetableOptions.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [fetchTimetablePeriods, 'periods'],
      [fetchTimetableRooms, 'rooms'],
      [fetchTimetableVersions, 'versions'],
      [fetchTimetableEntries, 'entries'],
      [fetchTimetableSubstitutions, 'substitutions'],
      [fetchTimetableExceptions, 'exceptions'],
      [fetchClassWeeklyTimetable, 'classWeekly'],
      [fetchStaffWeeklyTimetable, 'staffWeekly'],
      [fetchStaffDailyTimetable, 'staffDaily'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.loading = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.loading = false;
          state[key] = action.payload;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.loading = false;
          state.error = action.payload;
        });
    });

    [
      [createTimetablePeriod, 'periods'],
      [createTimetableRoom, 'rooms'],
      [createTimetableVersion, 'versions'],
      [createTimetableEntry, 'entries'],
      [createTimetableSubstitution, 'substitutions'],
      [createTimetableException, 'exceptions'],
      [updateTimetablePeriod, 'periods'],
      [updateTimetableRoom, 'rooms'],
      [updateTimetableVersion, 'versions'],
      [updateTimetableEntry, 'entries'],
      [updateTimetableSubstitution, 'substitutions'],
      [updateTimetableException, 'exceptions'],
      [publishTimetableVersion, 'versions'],
      [archiveTimetableVersion, 'versions'],
      [duplicateTimetableVersion, 'versions'],
      [approveTimetableSubstitution, 'substitutions'],
      [cancelTimetableSubstitution, 'substitutions'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = upsertItem(state[key], action.payload);
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [
      [deleteTimetablePeriod, 'periods'],
      [deleteTimetableRoom, 'rooms'],
      [deleteTimetableVersion, 'versions'],
      [deleteTimetableEntry, 'entries'],
      [deleteTimetableSubstitution, 'substitutions'],
      [deleteTimetableException, 'exceptions'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = state[key].filter((item) => item.id !== action.payload);
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(bulkCreateTimetableEntries.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(bulkCreateTimetableEntries.fulfilled, (state, action) => {
        state.saving = false;
        action.payload.forEach((entry) => {
          state.entries = upsertItem(state.entries, entry);
        });
      })
      .addCase(bulkCreateTimetableEntries.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(checkTimetableConflicts.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(checkTimetableConflicts.fulfilled, (state, action) => {
        state.saving = false;
        state.conflictCheck = action.payload;
      })
      .addCase(checkTimetableConflicts.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export const { clearTimetableConflictCheck } = timetableSlice.actions;

export default timetableSlice.reducer;
