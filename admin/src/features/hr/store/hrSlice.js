import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { hrApi } from '../services/hrApi';

const resourceApi = {
  departments: {
    list: hrApi.getDepartments,
    create: hrApi.createDepartment,
    update: hrApi.updateDepartment,
    delete: hrApi.deleteDepartment,
  },
  designations: {
    list: hrApi.getDesignations,
    create: hrApi.createDesignation,
    update: hrApi.updateDesignation,
    delete: hrApi.deleteDesignation,
  },
  staff: {
    list: hrApi.getStaff,
    create: hrApi.createStaff,
    update: hrApi.updateStaff,
    delete: hrApi.deleteStaff,
  },
  attendance: {
    list: hrApi.getAttendance,
    create: hrApi.createAttendance,
    update: hrApi.updateAttendance,
    delete: hrApi.deleteAttendance,
  },
  leaveTypes: {
    list: hrApi.getLeaveTypes,
    create: hrApi.createLeaveType,
    update: hrApi.updateLeaveType,
    delete: hrApi.deleteLeaveType,
  },
  leaveApplications: {
    list: hrApi.getLeaveApplications,
    create: hrApi.createLeaveApplication,
    update: hrApi.updateLeaveApplication,
    delete: hrApi.deleteLeaveApplication,
  },
  leaveBalances: {
    list: hrApi.getLeaveBalances,
  },
  salaryComponents: {
    list: hrApi.getSalaryComponents,
    create: hrApi.createSalaryComponent,
    update: hrApi.updateSalaryComponent,
    delete: hrApi.deleteSalaryComponent,
  },
  salaryStructures: {
    list: hrApi.getSalaryStructures,
    create: hrApi.createSalaryStructure,
    update: hrApi.updateSalaryStructure,
    delete: hrApi.deleteSalaryStructure,
  },
  payrollRuns: {
    list: hrApi.getPayrollRuns,
    create: hrApi.createPayrollRun,
    update: hrApi.updatePayrollRun,
    delete: hrApi.deletePayrollRun,
  },
  payslips: {
    list: hrApi.getPayslips,
    update: hrApi.updatePayslip,
  },
};

function createResourceState() {
  return {
    items: [],
    loading: false,
    saving: false,
    error: null,
    pagination: {
      page: 1,
      totalPages: 1,
      total: 0,
    },
    filters: {},
  };
}

function normalizeListResponse(payload) {
  const items = payload?.data || [];
  const meta = payload?.meta || {};

  return {
    items,
    pagination: {
      page: meta.current_page || 1,
      totalPages: meta.last_page || 1,
      total: meta.total || items.length,
    },
  };
}

export const fetchHrOptions = createAsyncThunk('hr/fetchHrOptions', async (_, thunkApi) => {
  try {
    const [departments, designations, staff, leaveTypes, salaryComponents] = await Promise.all([
      hrApi.getDepartments(),
      hrApi.getDesignations(),
      hrApi.getStaff({ per_page: 100 }),
      hrApi.getLeaveTypes(),
      hrApi.getSalaryComponents(),
    ]);

    return {
      departments: departments.data.data || [],
      designations: designations.data.data || [],
      staff: staff.data.data || [],
      leaveTypes: leaveTypes.data.data || [],
      salaryComponents: salaryComponents.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load HR options.');
  }
});

export const fetchHrResource = createAsyncThunk('hr/fetchHrResource', async ({ resource, params = {} }, thunkApi) => {
  try {
    const response = await resourceApi[resource].list(params);
    return { resource, ...normalizeListResponse(response.data) };
  } catch (error) {
    return thunkApi.rejectWithValue({
      resource,
      message: error.response?.data?.message || `Failed to load ${resource}.`,
    });
  }
});

export const createHrResource = createAsyncThunk('hr/createHrResource', async ({ resource, payload }, thunkApi) => {
  try {
    const response = await resourceApi[resource].create(payload);
    return { resource, item: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue({
      resource,
      message: error.response?.data?.message || `Failed to create ${resource}.`,
    });
  }
});

export const updateHrResource = createAsyncThunk('hr/updateHrResource', async ({ resource, id, payload }, thunkApi) => {
  try {
    const response = await resourceApi[resource].update(id, payload);
    return { resource, item: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue({
      resource,
      message: error.response?.data?.message || `Failed to update ${resource}.`,
    });
  }
});

export const deleteHrResource = createAsyncThunk('hr/deleteHrResource', async ({ resource, id }, thunkApi) => {
  try {
    await resourceApi[resource].delete(id);
    return { resource, id };
  } catch (error) {
    return thunkApi.rejectWithValue({
      resource,
      message: error.response?.data?.message || `Failed to delete ${resource}.`,
    });
  }
});

export const fetchStaffProfile = createAsyncThunk('hr/fetchStaffProfile', async (staffId, thunkApi) => {
  try {
    const response = await hrApi.getStaffProfile(staffId);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to load staff profile.');
  }
});

function profileThunk(type, request, fallbackMessage) {
  return createAsyncThunk(type, async (payload, thunkApi) => {
    try {
      const response = await request(payload);
      return response.data?.data ?? response.data;
    } catch (error) {
      return thunkApi.rejectWithValue(error.response?.data?.message || fallbackMessage);
    }
  });
}

export const uploadStaffDocument = profileThunk(
  'hr/uploadStaffDocument',
  ({ staffId, payload }) => hrApi.uploadStaffDocument(staffId, payload),
  'Failed to upload staff document.',
);
export const updateStaffDocument = profileThunk(
  'hr/updateStaffDocument',
  ({ documentId, payload }) => hrApi.updateStaffDocument(documentId, payload),
  'Failed to update staff document.',
);
export const deleteStaffDocument = createAsyncThunk('hr/deleteStaffDocument', async (documentId, thunkApi) => {
  try {
    await hrApi.deleteStaffDocument(documentId);
    return documentId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete staff document.');
  }
});

export const createStaffEmergencyContact = profileThunk(
  'hr/createStaffEmergencyContact',
  ({ staffId, payload }) => hrApi.createStaffEmergencyContact(staffId, payload),
  'Failed to save emergency contact.',
);
export const updateStaffEmergencyContact = profileThunk(
  'hr/updateStaffEmergencyContact',
  ({ contactId, payload }) => hrApi.updateStaffEmergencyContact(contactId, payload),
  'Failed to update emergency contact.',
);
export const deleteStaffEmergencyContact = createAsyncThunk('hr/deleteStaffEmergencyContact', async (contactId, thunkApi) => {
  try {
    await hrApi.deleteStaffEmergencyContact(contactId);
    return contactId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete emergency contact.');
  }
});

export const createStaffQualification = profileThunk(
  'hr/createStaffQualification',
  ({ staffId, payload }) => hrApi.createStaffQualification(staffId, payload),
  'Failed to save qualification.',
);
export const updateStaffQualification = profileThunk(
  'hr/updateStaffQualification',
  ({ qualificationId, payload }) => hrApi.updateStaffQualification(qualificationId, payload),
  'Failed to update qualification.',
);
export const deleteStaffQualification = createAsyncThunk('hr/deleteStaffQualification', async (qualificationId, thunkApi) => {
  try {
    await hrApi.deleteStaffQualification(qualificationId);
    return qualificationId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete qualification.');
  }
});

export const createStaffExperience = profileThunk(
  'hr/createStaffExperience',
  ({ staffId, payload }) => hrApi.createStaffExperience(staffId, payload),
  'Failed to save experience.',
);
export const updateStaffExperience = profileThunk(
  'hr/updateStaffExperience',
  ({ experienceId, payload }) => hrApi.updateStaffExperience(experienceId, payload),
  'Failed to update experience.',
);
export const deleteStaffExperience = createAsyncThunk('hr/deleteStaffExperience', async (experienceId, thunkApi) => {
  try {
    await hrApi.deleteStaffExperience(experienceId);
    return experienceId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete experience.');
  }
});

export const createStaffBankDetail = profileThunk(
  'hr/createStaffBankDetail',
  ({ staffId, payload }) => hrApi.createStaffBankDetail(staffId, payload),
  'Failed to save bank detail.',
);
export const updateStaffBankDetail = profileThunk(
  'hr/updateStaffBankDetail',
  ({ bankDetailId, payload }) => hrApi.updateStaffBankDetail(bankDetailId, payload),
  'Failed to update bank detail.',
);
export const deleteStaffBankDetail = createAsyncThunk('hr/deleteStaffBankDetail', async (bankDetailId, thunkApi) => {
  try {
    await hrApi.deleteStaffBankDetail(bankDetailId);
    return bankDetailId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete bank detail.');
  }
});

export const createStaffNote = profileThunk(
  'hr/createStaffNote',
  ({ staffId, payload }) => hrApi.createStaffNote(staffId, payload),
  'Failed to save staff note.',
);
export const updateStaffNote = profileThunk(
  'hr/updateStaffNote',
  ({ noteId, payload }) => hrApi.updateStaffNote(noteId, payload),
  'Failed to update staff note.',
);
export const deleteStaffNote = createAsyncThunk('hr/deleteStaffNote', async (noteId, thunkApi) => {
  try {
    await hrApi.deleteStaffNote(noteId);
    return noteId;
  } catch (error) {
    return thunkApi.rejectWithValue(error.response?.data?.message || 'Failed to delete staff note.');
  }
});

export const runStaffLifecycleAction = profileThunk(
  'hr/runStaffLifecycleAction',
  ({ staffId, action, payload }) => hrApi.runLifecycleAction(staffId, action, payload),
  'Failed to update staff status.',
);

export const runLeaveWorkflowAction = profileThunk(
  'hr/runLeaveWorkflowAction',
  ({ leaveApplicationId, action, payload }) => hrApi.runLeaveWorkflow(leaveApplicationId, action, payload),
  'Failed to update leave application.',
);

export const runPayrollWorkflowAction = profileThunk(
  'hr/runPayrollWorkflowAction',
  ({ payrollRunId, action }) => hrApi.runPayrollWorkflow(payrollRunId, action),
  'Failed to update payroll run.',
);

const initialState = {
  resources: {
    departments: createResourceState(),
    designations: createResourceState(),
    staff: createResourceState(),
    attendance: createResourceState(),
    leaveTypes: createResourceState(),
    leaveApplications: createResourceState(),
    leaveBalances: createResourceState(),
    salaryComponents: createResourceState(),
    salaryStructures: createResourceState(),
    payrollRuns: createResourceState(),
    payslips: createResourceState(),
  },
  options: {
    departments: [],
    designations: [],
    staff: [],
    leaveTypes: [],
    salaryComponents: [],
  },
  optionsLoading: false,
  optionsError: null,
  currentStaff: null,
  profileLoading: false,
  profileSaving: false,
  profileError: null,
};

const profilePendingMatchers = [
  uploadStaffDocument.pending,
  updateStaffDocument.pending,
  deleteStaffDocument.pending,
  createStaffEmergencyContact.pending,
  updateStaffEmergencyContact.pending,
  deleteStaffEmergencyContact.pending,
  createStaffQualification.pending,
  updateStaffQualification.pending,
  deleteStaffQualification.pending,
  createStaffExperience.pending,
  updateStaffExperience.pending,
  deleteStaffExperience.pending,
  createStaffBankDetail.pending,
  updateStaffBankDetail.pending,
  deleteStaffBankDetail.pending,
  createStaffNote.pending,
  updateStaffNote.pending,
  deleteStaffNote.pending,
  runStaffLifecycleAction.pending,
];

const profileRejectedMatchers = [
  uploadStaffDocument.rejected,
  updateStaffDocument.rejected,
  deleteStaffDocument.rejected,
  createStaffEmergencyContact.rejected,
  updateStaffEmergencyContact.rejected,
  deleteStaffEmergencyContact.rejected,
  createStaffQualification.rejected,
  updateStaffQualification.rejected,
  deleteStaffQualification.rejected,
  createStaffExperience.rejected,
  updateStaffExperience.rejected,
  deleteStaffExperience.rejected,
  createStaffBankDetail.rejected,
  updateStaffBankDetail.rejected,
  deleteStaffBankDetail.rejected,
  createStaffNote.rejected,
  updateStaffNote.rejected,
  deleteStaffNote.rejected,
  runStaffLifecycleAction.rejected,
];

const profileFulfilledMatchers = [
  uploadStaffDocument.fulfilled,
  updateStaffDocument.fulfilled,
  deleteStaffDocument.fulfilled,
  createStaffEmergencyContact.fulfilled,
  updateStaffEmergencyContact.fulfilled,
  deleteStaffEmergencyContact.fulfilled,
  createStaffQualification.fulfilled,
  updateStaffQualification.fulfilled,
  deleteStaffQualification.fulfilled,
  createStaffExperience.fulfilled,
  updateStaffExperience.fulfilled,
  deleteStaffExperience.fulfilled,
  createStaffBankDetail.fulfilled,
  updateStaffBankDetail.fulfilled,
  deleteStaffBankDetail.fulfilled,
  createStaffNote.fulfilled,
  updateStaffNote.fulfilled,
  deleteStaffNote.fulfilled,
];

const hrSlice = createSlice({
  name: 'hr',
  initialState,
  reducers: {
    setHrResourceFilters(state, action) {
      const { resource, filters } = action.payload;
      state.resources[resource].filters = {
        ...state.resources[resource].filters,
        ...filters,
      };
    },
    setHrResourcePage(state, action) {
      const { resource, page } = action.payload;
      state.resources[resource].pagination.page = page;
    },
    resetCurrentStaff(state) {
      state.currentStaff = null;
      state.profileError = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchHrOptions.pending, (state) => {
        state.optionsLoading = true;
        state.optionsError = null;
      })
      .addCase(fetchHrOptions.fulfilled, (state, action) => {
        state.optionsLoading = false;
        state.options = action.payload;
      })
      .addCase(fetchHrOptions.rejected, (state, action) => {
        state.optionsLoading = false;
        state.optionsError = action.payload;
      })
      .addCase(fetchHrResource.pending, (state, action) => {
        const resource = action.meta.arg.resource;
        state.resources[resource].loading = true;
        state.resources[resource].error = null;
      })
      .addCase(fetchHrResource.fulfilled, (state, action) => {
        const { resource, items, pagination } = action.payload;
        state.resources[resource].loading = false;
        state.resources[resource].items = items;
        state.resources[resource].pagination = pagination;
      })
      .addCase(fetchHrResource.rejected, (state, action) => {
        const { resource, message } = action.payload;
        state.resources[resource].loading = false;
        state.resources[resource].error = message;
      })
      .addCase(createHrResource.pending, (state, action) => {
        const resource = action.meta.arg.resource;
        state.resources[resource].saving = true;
        state.resources[resource].error = null;
      })
      .addCase(createHrResource.fulfilled, (state, action) => {
        const { resource, item } = action.payload;
        state.resources[resource].saving = false;
        state.resources[resource].items = [item, ...state.resources[resource].items];
      })
      .addCase(createHrResource.rejected, (state, action) => {
        const { resource, message } = action.payload;
        state.resources[resource].saving = false;
        state.resources[resource].error = message;
      })
      .addCase(updateHrResource.pending, (state, action) => {
        const resource = action.meta.arg.resource;
        state.resources[resource].saving = true;
        state.resources[resource].error = null;
      })
      .addCase(updateHrResource.fulfilled, (state, action) => {
        const { resource, item } = action.payload;
        state.resources[resource].saving = false;
        state.resources[resource].items = state.resources[resource].items.map((existing) =>
          existing.id === item.id ? item : existing,
        );
      })
      .addCase(updateHrResource.rejected, (state, action) => {
        const { resource, message } = action.payload;
        state.resources[resource].saving = false;
        state.resources[resource].error = message;
      })
      .addCase(deleteHrResource.pending, (state, action) => {
        const resource = action.meta.arg.resource;
        state.resources[resource].saving = true;
        state.resources[resource].error = null;
      })
      .addCase(deleteHrResource.fulfilled, (state, action) => {
        const { resource, id } = action.payload;
        state.resources[resource].saving = false;
        state.resources[resource].items = state.resources[resource].items.filter((item) => item.id !== id);
      })
      .addCase(deleteHrResource.rejected, (state, action) => {
        const { resource, message } = action.payload;
        state.resources[resource].saving = false;
        state.resources[resource].error = message;
      })
      .addCase(fetchStaffProfile.pending, (state) => {
        state.profileLoading = true;
        state.profileError = null;
      })
      .addCase(fetchStaffProfile.fulfilled, (state, action) => {
        state.profileLoading = false;
        state.currentStaff = action.payload;
      })
      .addCase(fetchStaffProfile.rejected, (state, action) => {
        state.profileLoading = false;
        state.profileError = action.payload;
      })
      .addCase(runStaffLifecycleAction.fulfilled, (state, action) => {
        state.profileSaving = false;
        state.currentStaff = action.payload;
      })
      .addCase(runLeaveWorkflowAction.fulfilled, (state) => {
        state.resources.leaveApplications.saving = false;
      })
      .addCase(runLeaveWorkflowAction.pending, (state) => {
        state.resources.leaveApplications.saving = true;
        state.resources.leaveApplications.error = null;
      })
      .addCase(runLeaveWorkflowAction.rejected, (state, action) => {
        state.resources.leaveApplications.saving = false;
        state.resources.leaveApplications.error = action.payload;
      })
      .addCase(runPayrollWorkflowAction.fulfilled, (state) => {
        state.resources.payrollRuns.saving = false;
      })
      .addCase(runPayrollWorkflowAction.pending, (state) => {
        state.resources.payrollRuns.saving = true;
        state.resources.payrollRuns.error = null;
      })
      .addCase(runPayrollWorkflowAction.rejected, (state, action) => {
        state.resources.payrollRuns.saving = false;
        state.resources.payrollRuns.error = action.payload;
      });

    profilePendingMatchers.forEach((matcher) => {
      builder.addCase(matcher, (state) => {
        state.profileSaving = true;
        state.profileError = null;
      });
    });

    profileFulfilledMatchers.forEach((matcher) => {
      builder.addCase(matcher, (state) => {
        state.profileSaving = false;
      });
    });

    profileRejectedMatchers.forEach((matcher) => {
      builder.addCase(matcher, (state, action) => {
        state.profileSaving = false;
        state.profileError = action.payload;
      });
    });
  },
});

export const { setHrResourceFilters, setHrResourcePage, resetCurrentStaff } = hrSlice.actions;
export default hrSlice.reducer;
