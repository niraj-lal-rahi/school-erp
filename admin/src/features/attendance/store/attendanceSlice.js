import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { attendanceApi } from '../services/attendanceApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function createAsyncResource(type, request, fallback) {
  return createAsyncThunk(type, async (payload, thunkApi) => {
    try {
      const response = await request(payload);
      return response.data.data ?? response.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

export const fetchAttendanceOptions = createAsyncThunk('attendance/fetchAttendanceOptions', async (_, thunkApi) => {
  try {
    const [statusTypes, periods, academicYears, schoolClasses, sections, students, staff, subjects] = await Promise.allSettled([
      attendanceApi.getStatusTypes(),
      attendanceApi.getPeriods(),
      attendanceApi.getAcademicYears(),
      attendanceApi.getClasses(),
      attendanceApi.getSections(),
      attendanceApi.getStudents({ per_page: 200 }),
      attendanceApi.getStaff({ per_page: 200 }),
      attendanceApi.getSubjects(),
    ]);

    function readCollection(result) {
      if (result.status !== 'fulfilled') {
        return [];
      }

      return result.value.data.data || [];
    }

    return {
      statusTypes: readCollection(statusTypes),
      periods: readCollection(periods),
      academicYears: readCollection(academicYears),
      schoolClasses: readCollection(schoolClasses),
      sections: readCollection(sections),
      students: readCollection(students),
      staff: readCollection(staff),
      subjects: readCollection(subjects),
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance options.'));
  }
});

export const fetchAttendanceStatusTypes = createAsyncThunk('attendance/fetchAttendanceStatusTypes', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getStatusTypes(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance status types.'));
  }
});

export const createAttendanceStatusType = createAsyncResource('attendance/createAttendanceStatusType', attendanceApi.createStatusType, 'Failed to create attendance status type.');
export const updateAttendanceStatusType = createAsyncThunk('attendance/updateAttendanceStatusType', async ({ id, payload }, thunkApi) => {
  try {
    const response = await attendanceApi.updateStatusType(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update attendance status type.'));
  }
});
export const deleteAttendanceStatusType = createAsyncThunk('attendance/deleteAttendanceStatusType', async (id, thunkApi) => {
  try {
    await attendanceApi.deleteStatusType(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete attendance status type.'));
  }
});

export const fetchAttendancePeriods = createAsyncThunk('attendance/fetchAttendancePeriods', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getPeriods(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance periods.'));
  }
});

export const fetchStudentSessions = createAsyncThunk('attendance/fetchStudentSessions', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getStudentSessions(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load student attendance sessions.'));
  }
});
export const fetchStudentSessionById = createAsyncThunk('attendance/fetchStudentSessionById', async (id, thunkApi) => {
  try {
    const response = await attendanceApi.getStudentSession(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance session details.'));
  }
});
export const createStudentSession = createAsyncResource('attendance/createStudentSession', attendanceApi.createStudentSession, 'Failed to create attendance session.');
export const updateStudentSession = createAsyncThunk('attendance/updateStudentSession', async ({ id, payload }, thunkApi) => {
  try {
    const response = await attendanceApi.updateStudentSession(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update attendance session.'));
  }
});
export const deleteStudentSession = createAsyncThunk('attendance/deleteStudentSession', async (id, thunkApi) => {
  try {
    await attendanceApi.deleteStudentSession(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete attendance session.'));
  }
});
export const bulkMarkStudentSession = createAsyncThunk('attendance/bulkMarkStudentSession', async ({ id, payload }, thunkApi) => {
  try {
    const response = await attendanceApi.bulkMarkSession(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to save student attendance records.'));
  }
});
export const submitStudentSession = createAsyncThunk('attendance/submitStudentSession', async (id, thunkApi) => {
  try {
    const response = await attendanceApi.submitStudentSession(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to submit attendance session.'));
  }
});
export const lockStudentSession = createAsyncThunk('attendance/lockStudentSession', async (id, thunkApi) => {
  try {
    const response = await attendanceApi.lockStudentSession(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to lock attendance session.'));
  }
});
export const fetchSessionStudents = createAsyncThunk('attendance/fetchSessionStudents', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getStudents({ ...params, per_page: 200 });
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load students for this session.'));
  }
});

export const fetchStaffRecords = createAsyncThunk('attendance/fetchStaffRecords', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getStaffRecords(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load staff attendance records.'));
  }
});
export const createStaffRecord = createAsyncResource('attendance/createStaffRecord', attendanceApi.createStaffRecord, 'Failed to create staff attendance record.');
export const updateStaffRecord = createAsyncThunk('attendance/updateStaffRecord', async ({ id, payload }, thunkApi) => {
  try {
    const response = await attendanceApi.updateStaffRecord(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update staff attendance record.'));
  }
});
export const deleteStaffRecord = createAsyncThunk('attendance/deleteStaffRecord', async (id, thunkApi) => {
  try {
    await attendanceApi.deleteStaffRecord(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete staff attendance record.'));
  }
});

export const fetchCorrections = createAsyncThunk('attendance/fetchCorrections', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getCorrections(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance correction requests.'));
  }
});
export const createCorrection = createAsyncResource('attendance/createCorrection', attendanceApi.createCorrection, 'Failed to create attendance correction request.');
export const approveCorrection = createAsyncThunk('attendance/approveCorrection', async ({ id, payload }, thunkApi) => {
  try {
    const response = await attendanceApi.approveCorrection(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to approve attendance correction.'));
  }
});
export const rejectCorrection = createAsyncThunk('attendance/rejectCorrection', async ({ id, payload }, thunkApi) => {
  try {
    const response = await attendanceApi.rejectCorrection(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to reject attendance correction.'));
  }
});

export const fetchHolidays = createAsyncThunk('attendance/fetchHolidays', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getHolidays(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load holidays.'));
  }
});
export const createHoliday = createAsyncResource('attendance/createHoliday', attendanceApi.createHoliday, 'Failed to create holiday.');
export const updateHoliday = createAsyncThunk('attendance/updateHoliday', async ({ id, payload }, thunkApi) => {
  try {
    const response = await attendanceApi.updateHoliday(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update holiday.'));
  }
});
export const deleteHoliday = createAsyncThunk('attendance/deleteHoliday', async (id, thunkApi) => {
  try {
    await attendanceApi.deleteHoliday(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete holiday.'));
  }
});

export const fetchAttendanceImports = createAsyncThunk('attendance/fetchAttendanceImports', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getImports(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance imports.'));
  }
});
export const createAttendanceImport = createAsyncResource('attendance/createAttendanceImport', attendanceApi.createImport, 'Failed to create attendance import.');
export const processAttendanceImport = createAsyncThunk('attendance/processAttendanceImport', async (id, thunkApi) => {
  try {
    const response = await attendanceApi.processImport(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to process attendance import.'));
  }
});

export const fetchBiometricLogs = createAsyncThunk('attendance/fetchBiometricLogs', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getBiometricLogs(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load biometric logs.'));
  }
});
export const createBiometricLog = createAsyncResource('attendance/createBiometricLog', attendanceApi.createBiometricLog, 'Failed to create biometric log.');
export const processBiometricLogs = createAsyncThunk('attendance/processBiometricLogs', async (payload = {}, thunkApi) => {
  try {
    await attendanceApi.processBiometricLogs(payload);
    return true;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to queue biometric log processing.'));
  }
});

export const fetchAttendanceSummary = createAsyncThunk('attendance/fetchAttendanceSummary', async (params = {}, thunkApi) => {
  try {
    const response = await attendanceApi.getSummary(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance summary.'));
  }
});
export const refreshAttendanceSummary = createAsyncThunk('attendance/refreshAttendanceSummary', async (payload = {}, thunkApi) => {
  try {
    const response = await attendanceApi.refreshSummary(payload);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to refresh attendance summary.'));
  }
});

export const fetchAttendanceReports = createAsyncThunk('attendance/fetchAttendanceReports', async (params = {}, thunkApi) => {
  try {
    const [studentSummary, staffSummary, classAttendance, defaulters] = await Promise.all([
      attendanceApi.getStudentSummaryReport(params),
      attendanceApi.getStaffSummaryReport(params),
      attendanceApi.getClassAttendanceReport(params),
      attendanceApi.getDefaultersReport(params),
    ]);

    return {
      studentSummary: studentSummary.data.data,
      staffSummary: staffSummary.data.data,
      classAttendance: classAttendance.data.data,
      defaulters: defaulters.data.data,
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load attendance reports.'));
  }
});

function upsert(items, payload) {
  const exists = items.some((item) => item.id === payload.id);
  if (!exists) {
    return [payload, ...items];
  }

  return items.map((item) => (item.id === payload.id ? payload : item));
}

const initialState = {
  options: {
    statusTypes: [],
    periods: [],
    academicYears: [],
    schoolClasses: [],
    sections: [],
    students: [],
    staff: [],
    subjects: [],
  },
  statusTypes: [],
  periods: [],
  studentSessions: [],
  currentSession: null,
  sessionStudents: [],
  staffRecords: [],
  corrections: [],
  holidays: [],
  imports: [],
  biometricLogs: [],
  summary: [],
  reports: {
    studentSummary: null,
    staffSummary: null,
    classAttendance: null,
    defaulters: null,
  },
  loading: false,
  saving: false,
  error: null,
};

const attendanceSlice = createSlice({
  name: 'attendance',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchAttendanceOptions.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchAttendanceOptions.fulfilled, (state, action) => {
        state.loading = false;
        state.options = action.payload;
        state.periods = action.payload.periods;
      })
      .addCase(fetchAttendanceOptions.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [fetchAttendanceStatusTypes, 'statusTypes'],
      [fetchAttendancePeriods, 'periods'],
      [fetchStudentSessions, 'studentSessions'],
      [fetchStaffRecords, 'staffRecords'],
      [fetchCorrections, 'corrections'],
      [fetchHolidays, 'holidays'],
      [fetchAttendanceImports, 'imports'],
      [fetchBiometricLogs, 'biometricLogs'],
      [fetchAttendanceSummary, 'summary'],
      [fetchSessionStudents, 'sessionStudents'],
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

    builder
      .addCase(fetchStudentSessionById.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchStudentSessionById.fulfilled, (state, action) => {
        state.loading = false;
        state.currentSession = action.payload;
        state.studentSessions = upsert(state.studentSessions, action.payload);
      })
      .addCase(fetchStudentSessionById.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchAttendanceReports.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchAttendanceReports.fulfilled, (state, action) => {
        state.loading = false;
        state.reports = action.payload;
      })
      .addCase(fetchAttendanceReports.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      createAttendanceStatusType,
      updateAttendanceStatusType,
      createStudentSession,
      updateStudentSession,
      bulkMarkStudentSession,
      submitStudentSession,
      lockStudentSession,
      createStaffRecord,
      updateStaffRecord,
      createCorrection,
      approveCorrection,
      rejectCorrection,
      createHoliday,
      updateHoliday,
      createAttendanceImport,
      processAttendanceImport,
      createBiometricLog,
    ].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(createAttendanceStatusType.fulfilled, (state, action) => {
        state.saving = false;
        state.statusTypes = upsert(state.statusTypes, action.payload);
        state.options.statusTypes = upsert(state.options.statusTypes, action.payload);
      })
      .addCase(updateAttendanceStatusType.fulfilled, (state, action) => {
        state.saving = false;
        state.statusTypes = upsert(state.statusTypes, action.payload);
        state.options.statusTypes = upsert(state.options.statusTypes, action.payload);
      })
      .addCase(deleteAttendanceStatusType.fulfilled, (state, action) => {
        state.statusTypes = state.statusTypes.filter((item) => item.id !== action.payload);
        state.options.statusTypes = state.options.statusTypes.filter((item) => item.id !== action.payload);
      })
      .addCase(createStudentSession.fulfilled, (state, action) => {
        state.saving = false;
        state.studentSessions = upsert(state.studentSessions, action.payload);
        state.currentSession = action.payload;
      })
      .addCase(updateStudentSession.fulfilled, (state, action) => {
        state.saving = false;
        state.studentSessions = upsert(state.studentSessions, action.payload);
        state.currentSession = action.payload;
      })
      .addCase(deleteStudentSession.fulfilled, (state, action) => {
        state.studentSessions = state.studentSessions.filter((item) => item.id !== action.payload);
        if (state.currentSession?.id === action.payload) {
          state.currentSession = null;
        }
      })
      .addCase(bulkMarkStudentSession.fulfilled, (state, action) => {
        state.saving = false;
        state.currentSession = action.payload;
        state.studentSessions = upsert(state.studentSessions, action.payload);
      })
      .addCase(submitStudentSession.fulfilled, (state, action) => {
        state.saving = false;
        state.currentSession = action.payload;
        state.studentSessions = upsert(state.studentSessions, action.payload);
      })
      .addCase(lockStudentSession.fulfilled, (state, action) => {
        state.saving = false;
        state.currentSession = action.payload;
        state.studentSessions = upsert(state.studentSessions, action.payload);
      })
      .addCase(createStaffRecord.fulfilled, (state, action) => {
        state.saving = false;
        state.staffRecords = upsert(state.staffRecords, action.payload);
      })
      .addCase(updateStaffRecord.fulfilled, (state, action) => {
        state.saving = false;
        state.staffRecords = upsert(state.staffRecords, action.payload);
      })
      .addCase(deleteStaffRecord.fulfilled, (state, action) => {
        state.staffRecords = state.staffRecords.filter((item) => item.id !== action.payload);
      })
      .addCase(createCorrection.fulfilled, (state, action) => {
        state.saving = false;
        state.corrections = upsert(state.corrections, action.payload);
      })
      .addCase(approveCorrection.fulfilled, (state, action) => {
        state.saving = false;
        state.corrections = upsert(state.corrections, action.payload);
      })
      .addCase(rejectCorrection.fulfilled, (state, action) => {
        state.saving = false;
        state.corrections = upsert(state.corrections, action.payload);
      })
      .addCase(createHoliday.fulfilled, (state, action) => {
        state.saving = false;
        state.holidays = upsert(state.holidays, action.payload);
      })
      .addCase(updateHoliday.fulfilled, (state, action) => {
        state.saving = false;
        state.holidays = upsert(state.holidays, action.payload);
      })
      .addCase(deleteHoliday.fulfilled, (state, action) => {
        state.holidays = state.holidays.filter((item) => item.id !== action.payload);
      })
      .addCase(createAttendanceImport.fulfilled, (state, action) => {
        state.saving = false;
        state.imports = upsert(state.imports, action.payload);
      })
      .addCase(processAttendanceImport.fulfilled, (state, action) => {
        state.saving = false;
        state.imports = upsert(state.imports, action.payload);
      })
      .addCase(createBiometricLog.fulfilled, (state, action) => {
        state.saving = false;
        state.biometricLogs = upsert(state.biometricLogs, action.payload);
      })
      .addCase(processBiometricLogs.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(processBiometricLogs.fulfilled, (state) => {
        state.saving = false;
      })
      .addCase(processBiometricLogs.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(refreshAttendanceSummary.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(refreshAttendanceSummary.fulfilled, (state, action) => {
        state.saving = false;
        state.summary = action.payload;
      })
      .addCase(refreshAttendanceSummary.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default attendanceSlice.reducer;
