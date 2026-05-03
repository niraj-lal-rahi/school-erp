import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { settingsApi } from '../services/settingsApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function toArrayPayload(response) {
  const payload = response.data?.data;
  return Array.isArray(payload) ? payload : [];
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

export const fetchSettingGroups = createAsyncThunk('settings/fetchSettingGroups', async (params = {}, thunkApi) => {
  try {
    const response = await settingsApi.getGroups(params);
    return toArrayPayload(response);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load setting groups.'));
  }
});

export const createSettingGroup = createMutationThunk('settings/createSettingGroup', settingsApi.createGroup, 'Failed to create setting group.');
export const updateSettingGroup = createUpdateThunk('settings/updateSettingGroup', settingsApi.updateGroup, 'Failed to update setting group.');
export const deleteSettingGroup = createDeleteThunk('settings/deleteSettingGroup', settingsApi.deleteGroup, 'Failed to delete setting group.');

export const fetchSettings = createAsyncThunk('settings/fetchSettings', async (params = {}, thunkApi) => {
  try {
    const response = await settingsApi.getSettings(params);
    return toArrayPayload(response);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load settings.'));
  }
});

export const createSetting = createMutationThunk('settings/createSetting', settingsApi.createSetting, 'Failed to create setting.');
export const updateSetting = createUpdateThunk('settings/updateSetting', settingsApi.updateSetting, 'Failed to update setting.');

export const fetchFeatureFlags = createAsyncThunk('settings/fetchFeatureFlags', async (params = {}, thunkApi) => {
  try {
    const response = await settingsApi.getFeatures(params);
    return toArrayPayload(response);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load feature flags.'));
  }
});

export const updateFeatureFlag = createUpdateThunk('settings/updateFeatureFlag', settingsApi.updateFeature, 'Failed to update feature flag.');
export const enableFeatureFlag = createAsyncThunk('settings/enableFeatureFlag', async (id, thunkApi) => {
  try {
    const response = await settingsApi.enableFeature(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to enable feature flag.'));
  }
});
export const disableFeatureFlag = createAsyncThunk('settings/disableFeatureFlag', async (id, thunkApi) => {
  try {
    const response = await settingsApi.disableFeature(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to disable feature flag.'));
  }
});

export const fetchBranding = createAsyncThunk('settings/fetchBranding', async (_, thunkApi) => {
  try {
    const response = await settingsApi.getBranding();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load branding settings.'));
  }
});

export const updateBranding = createMutationThunk('settings/updateBranding', settingsApi.updateBranding, 'Failed to update branding settings.');

export const fetchLocalization = createAsyncThunk('settings/fetchLocalization', async (_, thunkApi) => {
  try {
    const response = await settingsApi.getLocalization();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load localization settings.'));
  }
});

export const updateLocalization = createMutationThunk('settings/updateLocalization', settingsApi.updateLocalization, 'Failed to update localization settings.');

export const fetchSecurity = createAsyncThunk('settings/fetchSecurity', async (_, thunkApi) => {
  try {
    const response = await settingsApi.getSecurity();
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load security settings.'));
  }
});

export const updateSecurity = createMutationThunk('settings/updateSecurity', settingsApi.updateSecurity, 'Failed to update security settings.');

export const fetchIntegrations = createAsyncThunk('settings/fetchIntegrations', async (params = {}, thunkApi) => {
  try {
    const response = await settingsApi.getIntegrations(params);
    return toArrayPayload(response);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load integration settings.'));
  }
});

export const createIntegration = createMutationThunk('settings/createIntegration', settingsApi.createIntegration, 'Failed to create integration setting.');
export const updateIntegration = createUpdateThunk('settings/updateIntegration', settingsApi.updateIntegration, 'Failed to update integration setting.');
export const deleteIntegration = createDeleteThunk('settings/deleteIntegration', settingsApi.deleteIntegration, 'Failed to delete integration setting.');

export const fetchAuditLogs = createAsyncThunk('settings/fetchAuditLogs', async (params = {}, thunkApi) => {
  try {
    const response = await settingsApi.getAuditLogs(params);
    return toArrayPayload(response);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load setting audit logs.'));
  }
});

export const fetchPublicConfig = createAsyncThunk('settings/fetchPublicConfig', async (_, thunkApi) => {
  try {
    const response = await settingsApi.getPublicConfig();
    return response.data.data || null;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load public config.'));
  }
});

const initialState = {
  groups: [],
  settings: [],
  features: [],
  branding: null,
  localization: null,
  security: null,
  integrations: [],
  auditLogs: [],
  publicConfig: null,
  loading: false,
  saving: false,
  error: null,
};

const settingsSlice = createSlice({
  name: 'settings',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    [
      [fetchSettingGroups, 'groups'],
      [fetchSettings, 'settings'],
      [fetchFeatureFlags, 'features'],
      [fetchIntegrations, 'integrations'],
      [fetchAuditLogs, 'auditLogs'],
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
      [fetchBranding, 'branding'],
      [fetchLocalization, 'localization'],
      [fetchSecurity, 'security'],
      [fetchPublicConfig, 'publicConfig'],
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
      [createSettingGroup, 'groups'],
      [updateSettingGroup, 'groups'],
      [createSetting, 'settings'],
      [updateSetting, 'settings'],
      [updateFeatureFlag, 'features'],
      [enableFeatureFlag, 'features'],
      [disableFeatureFlag, 'features'],
      [createIntegration, 'integrations'],
      [updateIntegration, 'integrations'],
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

    [updateBranding, updateLocalization, updateSecurity].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;

          if (thunk === updateBranding) {
            state.branding = action.payload;
          } else if (thunk === updateLocalization) {
            state.localization = action.payload;
          } else {
            state.security = action.payload;
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [deleteSettingGroup, deleteIntegration].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;

          if (thunk === deleteSettingGroup) {
            state.groups = state.groups.filter((item) => item.id !== action.payload);
          } else {
            state.integrations = state.integrations.filter((item) => item.id !== action.payload);
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });
  },
});

export default settingsSlice.reducer;
