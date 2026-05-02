import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { saasApi } from '../services/saasApi';

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

export const fetchTenants = createAsyncThunk('saas/fetchTenants', async (params = {}, thunkApi) => {
  try {
    const response = await saasApi.getTenants(params);
    return normalizePaginated(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load tenants.'));
  }
});

export const fetchTenant = createAsyncThunk('saas/fetchTenant', async (id, thunkApi) => {
  try {
    const response = await saasApi.getTenant(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load tenant.'));
  }
});

export const createTenant = createMutationThunk('saas/createTenant', saasApi.createTenant, 'Failed to create tenant.');
export const onboardSchool = createMutationThunk('saas/onboardSchool', saasApi.onboardSchool, 'Failed to onboard school.');
export const updateTenant = createUpdateThunk('saas/updateTenant', saasApi.updateTenant, 'Failed to update tenant.');
export const activateTenant = createAsyncThunk('saas/activateTenant', async (id, thunkApi) => {
  try {
    const response = await saasApi.activateTenant(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to activate tenant.'));
  }
});
export const suspendTenant = createAsyncThunk('saas/suspendTenant', async (id, thunkApi) => {
  try {
    const response = await saasApi.suspendTenant(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to suspend tenant.'));
  }
});
export const cancelTenant = createAsyncThunk('saas/cancelTenant', async (id, thunkApi) => {
  try {
    const response = await saasApi.cancelTenant(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to cancel tenant.'));
  }
});

export const fetchPlans = createAsyncThunk('saas/fetchPlans', async (params = {}, thunkApi) => {
  try {
    const response = await saasApi.getPlans(params);
    const payload = response.data.data;

    if (Array.isArray(payload)) {
      return {
        items: payload,
        pagination: { page: 1, totalPages: 1, total: payload.length },
      };
    }

    return normalizePaginated(payload);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load plans.'));
  }
});

export const createPlan = createMutationThunk('saas/createPlan', saasApi.createPlan, 'Failed to create plan.');
export const updatePlan = createUpdateThunk('saas/updatePlan', saasApi.updatePlan, 'Failed to update plan.');
export const deletePlan = createDeleteThunk('saas/deletePlan', saasApi.deletePlan, 'Failed to delete plan.');
export const syncPlanFeatures = createUpdateThunk('saas/syncPlanFeatures', saasApi.syncPlanFeatures, 'Failed to sync plan features.');

export const subscribeTenant = createUpdateThunk('saas/subscribeTenant', saasApi.subscribeTenant, 'Failed to subscribe tenant.');
export const changeTenantPlan = createUpdateThunk('saas/changeTenantPlan', saasApi.changeTenantPlan, 'Failed to change tenant plan.');
export const renewTenantPlan = createUpdateThunk('saas/renewTenantPlan', saasApi.renewTenantPlan, 'Failed to renew tenant plan.');
export const cancelTenantSubscription = createUpdateThunk('saas/cancelTenantSubscription', saasApi.cancelTenantSubscription, 'Failed to cancel tenant subscription.');

export const fetchTenantFeatures = createAsyncThunk('saas/fetchTenantFeatures', async (id, thunkApi) => {
  try {
    const response = await saasApi.getTenantFeatures(id);
    return {
      tenantId: id,
      features: response.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load tenant features.'));
  }
});

export const updateTenantFeatures = createUpdateThunk('saas/updateTenantFeatures', saasApi.updateTenantFeatures, 'Failed to update tenant features.');

export const fetchTenantUsage = createAsyncThunk('saas/fetchTenantUsage', async (id, thunkApi) => {
  try {
    const response = await saasApi.getTenantUsage(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load tenant usage.'));
  }
});

export const syncTenantUsage = createAsyncThunk('saas/syncTenantUsage', async (id, thunkApi) => {
  try {
    const response = await saasApi.syncTenantUsage(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to sync tenant usage.'));
  }
});

export const fetchTenantBilling = createAsyncThunk('saas/fetchTenantBilling', async (id, thunkApi) => {
  try {
    const response = await saasApi.getTenantBilling(id);
    return {
      tenantId: id,
      items: response.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load billing records.'));
  }
});

export const markBillingPaid = createAsyncThunk('saas/markBillingPaid', async (id, thunkApi) => {
  try {
    const response = await saasApi.markBillingPaid(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark billing record paid.'));
  }
});

export const markBillingFailed = createAsyncThunk('saas/markBillingFailed', async (id, thunkApi) => {
  try {
    const response = await saasApi.markBillingFailed(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark billing record failed.'));
  }
});

export const fetchTenantDomains = createAsyncThunk('saas/fetchTenantDomains', async (id, thunkApi) => {
  try {
    const response = await saasApi.getTenantDomains(id);
    return {
      tenantId: id,
      items: response.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load tenant domains.'));
  }
});

export const createTenantDomain = createUpdateThunk('saas/createTenantDomain', saasApi.createTenantDomain, 'Failed to create tenant domain.');
export const verifyTenantDomain = createAsyncThunk('saas/verifyTenantDomain', async (id, thunkApi) => {
  try {
    const response = await saasApi.verifyTenantDomain(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to verify domain.'));
  }
});

const initialState = {
  tenants: [],
  plans: [],
  selectedTenant: null,
  selectedUsage: null,
  selectedFeatures: [],
  selectedBilling: [],
  selectedDomains: [],
  tenantsPagination: { page: 1, totalPages: 1, total: 0 },
  plansPagination: { page: 1, totalPages: 1, total: 0 },
  loading: false,
  saving: false,
  error: null,
};

const saasSlice = createSlice({
  name: 'saas',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    [
      [fetchTenants, 'tenants', 'tenantsPagination'],
      [fetchPlans, 'plans', 'plansPagination'],
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
      .addCase(fetchTenant.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTenant.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedTenant = action.payload;
        state.tenants = upsertItem(state.tenants, action.payload);
      })
      .addCase(fetchTenant.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchTenantFeatures.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTenantFeatures.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedFeatures = action.payload.features;
      })
      .addCase(fetchTenantFeatures.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchTenantUsage.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTenantUsage.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedUsage = action.payload;
      })
      .addCase(fetchTenantUsage.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchTenantBilling.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTenantBilling.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedBilling = action.payload.items;
      })
      .addCase(fetchTenantBilling.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchTenantDomains.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchTenantDomains.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedDomains = action.payload.items;
      })
      .addCase(fetchTenantDomains.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [createTenant, 'tenants'],
      [updateTenant, 'tenants'],
      [activateTenant, 'tenants'],
      [suspendTenant, 'tenants'],
      [cancelTenant, 'tenants'],
      [createPlan, 'plans'],
      [updatePlan, 'plans'],
      [syncPlanFeatures, 'plans'],
      [subscribeTenant, 'tenants'],
      [changeTenantPlan, 'tenants'],
      [renewTenantPlan, 'tenants'],
      [cancelTenantSubscription, 'tenants'],
      [updateTenantFeatures, 'selectedFeatures'],
      [syncTenantUsage, 'selectedUsage'],
      [markBillingPaid, 'selectedBilling'],
      [markBillingFailed, 'selectedBilling'],
      [createTenantDomain, 'selectedDomains'],
      [verifyTenantDomain, 'selectedDomains'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;

          if (key === 'selectedUsage') {
            state.selectedUsage = action.payload;
            return;
          }

          if (key === 'selectedFeatures') {
            state.selectedFeatures = Array.isArray(action.payload) ? action.payload : (action.payload.features || []);
            return;
          }

          if (key === 'selectedBilling') {
            state.selectedBilling = upsertItem(state.selectedBilling, action.payload);
            return;
          }

          if (key === 'selectedDomains') {
            state.selectedDomains = upsertItem(state.selectedDomains, action.payload);
            return;
          }

          if (key === 'tenants') {
            const tenantPayload = action.payload?.tenant || action.payload;
            state.tenants = upsertItem(state.tenants, tenantPayload);
            state.selectedTenant = tenantPayload;
            return;
          }

          state[key] = upsertItem(state[key], action.payload);
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(onboardSchool.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(onboardSchool.fulfilled, (state, action) => {
        state.saving = false;
        state.tenants = upsertItem(state.tenants, action.payload.tenant);
        state.selectedTenant = action.payload.tenant;
      })
      .addCase(onboardSchool.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(deletePlan.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(deletePlan.fulfilled, (state, action) => {
        state.saving = false;
        state.plans = state.plans.filter((item) => item.id !== action.payload);
      })
      .addCase(deletePlan.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default saasSlice.reducer;
