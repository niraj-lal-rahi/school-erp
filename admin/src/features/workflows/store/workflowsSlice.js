import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { workflowsApi } from '../services/workflowsApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function normalizeCollection(payload) {
  if (Array.isArray(payload)) {
    return {
      items: payload,
      pagination: { page: 1, totalPages: 1, total: payload.length },
    };
  }

  if (Array.isArray(payload?.data)) {
    return {
      items: payload.data,
      pagination: {
        page: payload.current_page || 1,
        totalPages: payload.last_page || 1,
        total: payload.total || payload.data.length,
      },
    };
  }

  return {
    items: [],
    pagination: { page: 1, totalPages: 1, total: 0 },
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

export const fetchWorkflowDefinitions = createAsyncThunk('workflows/fetchDefinitions', async (params = {}, thunkApi) => {
  try {
    const response = await workflowsApi.getDefinitions(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load workflow definitions.'));
  }
});

export const fetchWorkflowDefinition = createAsyncThunk('workflows/fetchDefinition', async (id, thunkApi) => {
  try {
    const response = await workflowsApi.getDefinition(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load workflow definition.'));
  }
});

export const createWorkflowDefinition = createMutationThunk('workflows/createDefinition', workflowsApi.createDefinition, 'Failed to create workflow definition.');
export const updateWorkflowDefinition = createUpdateThunk('workflows/updateDefinition', workflowsApi.updateDefinition, 'Failed to update workflow definition.');
export const deleteWorkflowDefinition = createAsyncThunk('workflows/deleteDefinition', async (id, thunkApi) => {
  try {
    await workflowsApi.deleteDefinition(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete workflow definition.'));
  }
});
export const activateWorkflowDefinition = createAsyncThunk('workflows/activateDefinition', async (id, thunkApi) => {
  try {
    const response = await workflowsApi.activateDefinition(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to activate workflow definition.'));
  }
});
export const deactivateWorkflowDefinition = createAsyncThunk('workflows/deactivateDefinition', async (id, thunkApi) => {
  try {
    const response = await workflowsApi.deactivateDefinition(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to deactivate workflow definition.'));
  }
});
export const createWorkflowStep = createAsyncThunk('workflows/createStep', async ({ definitionId, payload }, thunkApi) => {
  try {
    const response = await workflowsApi.createStep(definitionId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to create workflow step.'));
  }
});
export const updateWorkflowStep = createUpdateThunk('workflows/updateStep', workflowsApi.updateStep, 'Failed to update workflow step.');
export const deleteWorkflowStep = createAsyncThunk('workflows/deleteStep', async (id, thunkApi) => {
  try {
    await workflowsApi.deleteStep(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete workflow step.'));
  }
});

export const startWorkflow = createMutationThunk('workflows/startWorkflow', workflowsApi.startWorkflow, 'Failed to start workflow.');
export const fetchWorkflowInstances = createAsyncThunk('workflows/fetchInstances', async (params = {}, thunkApi) => {
  try {
    const response = await workflowsApi.getInstances(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load workflow instances.'));
  }
});
export const cancelWorkflowInstance = createUpdateThunk('workflows/cancelInstance', workflowsApi.cancelInstance, 'Failed to cancel workflow instance.');

export const fetchApprovalRequests = createAsyncThunk('workflows/fetchApprovals', async (params = {}, thunkApi) => {
  try {
    const response = await workflowsApi.getApprovals(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load approval requests.'));
  }
});
export const approveWorkflowRequest = createUpdateThunk('workflows/approveRequest', workflowsApi.approveRequest, 'Failed to approve request.');
export const rejectWorkflowRequest = createUpdateThunk('workflows/rejectRequest', workflowsApi.rejectRequest, 'Failed to reject request.');

export const fetchAutomationRules = createAsyncThunk('workflows/fetchAutomations', async (params = {}, thunkApi) => {
  try {
    const response = await workflowsApi.getAutomations(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load automation rules.'));
  }
});
export const createAutomationRule = createMutationThunk('workflows/createAutomation', workflowsApi.createAutomation, 'Failed to create automation rule.');
export const updateAutomationRule = createUpdateThunk('workflows/updateAutomation', workflowsApi.updateAutomation, 'Failed to update automation rule.');
export const deleteAutomationRule = createAsyncThunk('workflows/deleteAutomation', async (id, thunkApi) => {
  try {
    await workflowsApi.deleteAutomation(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete automation rule.'));
  }
});
export const runAutomationRule = createUpdateThunk('workflows/runAutomation', workflowsApi.runAutomation, 'Failed to run automation rule.');
export const activateAutomationRule = createAsyncThunk('workflows/activateAutomation', async (id, thunkApi) => {
  try {
    const response = await workflowsApi.activateAutomation(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to activate automation rule.'));
  }
});
export const deactivateAutomationRule = createAsyncThunk('workflows/deactivateAutomation', async (id, thunkApi) => {
  try {
    const response = await workflowsApi.deactivateAutomation(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to deactivate automation rule.'));
  }
});
export const fetchAutomationRuns = createAsyncThunk('workflows/fetchAutomationRuns', async (params = {}, thunkApi) => {
  try {
    const response = await workflowsApi.getAutomationRuns(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load automation runs.'));
  }
});

export const fetchReminderRules = createAsyncThunk('workflows/fetchReminders', async (params = {}, thunkApi) => {
  try {
    const response = await workflowsApi.getReminders(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load reminder rules.'));
  }
});
export const createReminderRule = createMutationThunk('workflows/createReminder', workflowsApi.createReminder, 'Failed to create reminder rule.');
export const updateReminderRule = createUpdateThunk('workflows/updateReminder', workflowsApi.updateReminder, 'Failed to update reminder rule.');
export const deleteReminderRule = createAsyncThunk('workflows/deleteReminder', async (id, thunkApi) => {
  try {
    await workflowsApi.deleteReminder(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete reminder rule.'));
  }
});
export const processDueReminderRules = createAsyncThunk('workflows/processDueReminders', async (_, thunkApi) => {
  try {
    const response = await workflowsApi.processDueReminders();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to process due reminders.'));
  }
});

export const fetchWorkflowReports = createAsyncThunk('workflows/fetchReports', async (_, thunkApi) => {
  try {
    const [workflowSummary, automationSummary, approvalPending] = await Promise.all([
      workflowsApi.getWorkflowSummary(),
      workflowsApi.getAutomationSummary(),
      workflowsApi.getApprovalPendingSummary(),
    ]);

    return {
      workflowSummary: workflowSummary.data.data,
      automationSummary: automationSummary.data.data,
      approvalPending: approvalPending.data.data,
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load workflow reports.'));
  }
});

const initialState = {
  definitions: [],
  selectedDefinition: null,
  instances: [],
  approvals: [],
  automations: [],
  automationRuns: [],
  reminders: [],
  reports: {
    workflowSummary: null,
    automationSummary: null,
    approvalPending: null,
  },
  definitionsPagination: { page: 1, totalPages: 1, total: 0 },
  instancesPagination: { page: 1, totalPages: 1, total: 0 },
  approvalsPagination: { page: 1, totalPages: 1, total: 0 },
  automationsPagination: { page: 1, totalPages: 1, total: 0 },
  automationRunsPagination: { page: 1, totalPages: 1, total: 0 },
  remindersPagination: { page: 1, totalPages: 1, total: 0 },
  lastStartedWorkflow: null,
  lastAutomationRun: null,
  reminderProcessingResult: null,
  loading: false,
  saving: false,
  error: null,
};

const workflowsSlice = createSlice({
  name: 'workflows',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    [
      [fetchWorkflowDefinitions, 'definitions', 'definitionsPagination'],
      [fetchWorkflowInstances, 'instances', 'instancesPagination'],
      [fetchApprovalRequests, 'approvals', 'approvalsPagination'],
      [fetchAutomationRules, 'automations', 'automationsPagination'],
      [fetchAutomationRuns, 'automationRuns', 'automationRunsPagination'],
      [fetchReminderRules, 'reminders', 'remindersPagination'],
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

    builder
      .addCase(fetchWorkflowDefinition.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchWorkflowDefinition.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedDefinition = action.payload;
        state.definitions = upsertItem(state.definitions, action.payload);
      })
      .addCase(fetchWorkflowDefinition.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchWorkflowReports.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchWorkflowReports.fulfilled, (state, action) => {
        state.loading = false;
        state.reports = action.payload;
      })
      .addCase(fetchWorkflowReports.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [createWorkflowDefinition, 'definitions'],
      [updateWorkflowDefinition, 'definitions'],
      [activateWorkflowDefinition, 'definitions'],
      [deactivateWorkflowDefinition, 'definitions'],
      [cancelWorkflowInstance, 'instances'],
      [approveWorkflowRequest, 'approvals'],
      [rejectWorkflowRequest, 'approvals'],
      [createAutomationRule, 'automations'],
      [updateAutomationRule, 'automations'],
      [activateAutomationRule, 'automations'],
      [deactivateAutomationRule, 'automations'],
      [createReminderRule, 'reminders'],
      [updateReminderRule, 'reminders'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = upsertItem(state[key], action.payload);
          if (key === 'definitions' && state.selectedDefinition?.id === action.payload.id) {
            state.selectedDefinition = action.payload;
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(deleteWorkflowDefinition.fulfilled, (state, action) => {
        state.saving = false;
        state.definitions = state.definitions.filter((item) => item.id !== action.payload);
        if (state.selectedDefinition?.id === action.payload) {
          state.selectedDefinition = null;
        }
      })
      .addCase(deleteAutomationRule.fulfilled, (state, action) => {
        state.saving = false;
        state.automations = state.automations.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteReminderRule.fulfilled, (state, action) => {
        state.saving = false;
        state.reminders = state.reminders.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteWorkflowStep.fulfilled, (state, action) => {
        state.saving = false;
        if (state.selectedDefinition?.steps) {
          state.selectedDefinition.steps = state.selectedDefinition.steps.filter((item) => item.id !== action.payload);
        }
      })
      .addCase(createWorkflowStep.fulfilled, (state, action) => {
        state.saving = false;
        if (state.selectedDefinition) {
          state.selectedDefinition = {
            ...state.selectedDefinition,
            steps: [...(state.selectedDefinition.steps || []), action.payload].sort((a, b) => a.sequence - b.sequence),
          };
        }
      })
      .addCase(updateWorkflowStep.fulfilled, (state, action) => {
        state.saving = false;
        if (state.selectedDefinition?.steps) {
          state.selectedDefinition.steps = state.selectedDefinition.steps
            .map((item) => (item.id === action.payload.id ? action.payload : item))
            .sort((a, b) => a.sequence - b.sequence);
        }
      })
      .addCase(startWorkflow.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(startWorkflow.fulfilled, (state, action) => {
        state.saving = false;
        state.lastStartedWorkflow = action.payload;
        state.instances = upsertItem(state.instances, action.payload);
      })
      .addCase(startWorkflow.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(runAutomationRule.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(runAutomationRule.fulfilled, (state, action) => {
        state.saving = false;
        state.lastAutomationRun = action.payload;
        state.automationRuns = upsertItem(state.automationRuns, action.payload);
      })
      .addCase(runAutomationRule.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(processDueReminderRules.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(processDueReminderRules.fulfilled, (state, action) => {
        state.saving = false;
        state.reminderProcessingResult = action.payload;
      })
      .addCase(processDueReminderRules.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });

    [
      deleteWorkflowDefinition,
      deleteAutomationRule,
      deleteReminderRule,
      deleteWorkflowStep,
      createWorkflowStep,
      updateWorkflowStep,
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
  },
});

export default workflowsSlice.reducer;
