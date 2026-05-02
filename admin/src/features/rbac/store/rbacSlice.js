import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { rbacApi } from '../services/rbacApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function normalizePaginated(payload) {
  return {
    items: payload?.data || [],
    pagination: {
      page: payload?.meta?.current_page || 1,
      totalPages: payload?.meta?.last_page || 1,
      total: payload?.meta?.total || 0,
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
  return createAsyncThunk(type, async ({ id, extra }, thunkApi) => {
    try {
      await request(id, extra);
      return { id, extra };
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallback));
    }
  });
}

export const fetchRoles = createAsyncThunk('rbac/fetchRoles', async (params = {}, thunkApi) => {
  try {
    const response = await rbacApi.getRoles(params);
    return normalizePaginated(response.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load roles.'));
  }
});

export const createRole = createMutationThunk('rbac/createRole', rbacApi.createRole, 'Failed to create role.');
export const updateRole = createUpdateThunk('rbac/updateRole', rbacApi.updateRole, 'Failed to update role.');
export const cloneRole = createUpdateThunk('rbac/cloneRole', rbacApi.cloneRole, 'Failed to clone role.');
export const deleteRole = createDeleteThunk(
  'rbac/deleteRole',
  async (id) => rbacApi.deleteRole(id),
  'Failed to delete role.',
);
export const syncRolePermissions = createUpdateThunk(
  'rbac/syncRolePermissions',
  rbacApi.syncRolePermissions,
  'Failed to sync role permissions.',
);

export const fetchPermissions = createAsyncThunk('rbac/fetchPermissions', async (params = {}, thunkApi) => {
  try {
    const response = await rbacApi.getPermissions(params);
    return normalizePaginated(response.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load permissions.'));
  }
});

export const fetchGroupedPermissions = createAsyncThunk('rbac/fetchGroupedPermissions', async (params = {}, thunkApi) => {
  try {
    const response = await rbacApi.getGroupedPermissions(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load grouped permissions.'));
  }
});

export const syncPermissionsCatalog = createAsyncThunk('rbac/syncPermissionsCatalog', async (_, thunkApi) => {
  try {
    const response = await rbacApi.syncPermissionsCatalog();
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to synchronize permissions.'));
  }
});

export const fetchUserRoles = createAsyncThunk('rbac/fetchUserRoles', async (userId, thunkApi) => {
  try {
    const response = await rbacApi.getUserRoles(userId);
    return {
      userId,
      roles: response.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load user roles.'));
  }
});

export const assignUserRole = createAsyncThunk('rbac/assignUserRole', async ({ userId, payload }, thunkApi) => {
  try {
    const response = await rbacApi.assignUserRole(userId, payload);
    return {
      userId,
      assignment: response.data.data,
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to assign role to user.'));
  }
});

export const removeUserRole = createAsyncThunk('rbac/removeUserRole', async ({ userId, roleId }, thunkApi) => {
  try {
    await rbacApi.removeUserRole(userId, roleId);
    return { userId, roleId };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to remove role from user.'));
  }
});

export const fetchMyPermissions = createAsyncThunk('rbac/fetchMyPermissions', async (_, thunkApi) => {
  try {
    const response = await rbacApi.getMyPermissions();
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load your permissions.'));
  }
});

export const fetchMyRoles = createAsyncThunk('rbac/fetchMyRoles', async (_, thunkApi) => {
  try {
    const response = await rbacApi.getMyRoles();
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load your roles.'));
  }
});

export const checkPermission = createMutationThunk('rbac/checkPermission', rbacApi.checkPermission, 'Failed to check permission.');

const initialState = {
  roles: [],
  permissions: [],
  groupedPermissions: [],
  selectedUserRoles: [],
  myPermissions: [],
  myRoles: [],
  lastPermissionCheck: null,
  selectedUserId: '',
  rolesPagination: { page: 1, totalPages: 1, total: 0 },
  permissionsPagination: { page: 1, totalPages: 1, total: 0 },
  loading: false,
  saving: false,
  error: null,
};

const rbacSlice = createSlice({
  name: 'rbac',
  initialState,
  reducers: {
    setSelectedUserId(state, action) {
      state.selectedUserId = action.payload;
    },
    clearPermissionCheck(state) {
      state.lastPermissionCheck = null;
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchRoles.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchRoles.fulfilled, (state, action) => {
        state.loading = false;
        state.roles = action.payload.items;
        state.rolesPagination = action.payload.pagination;
      })
      .addCase(fetchRoles.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchPermissions.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPermissions.fulfilled, (state, action) => {
        state.loading = false;
        state.permissions = action.payload.items;
        state.permissionsPagination = action.payload.pagination;
      })
      .addCase(fetchPermissions.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchGroupedPermissions.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchGroupedPermissions.fulfilled, (state, action) => {
        state.loading = false;
        state.groupedPermissions = action.payload;
      })
      .addCase(fetchGroupedPermissions.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchUserRoles.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchUserRoles.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedUserId = String(action.payload.userId);
        state.selectedUserRoles = action.payload.roles;
      })
      .addCase(fetchUserRoles.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchMyPermissions.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchMyPermissions.fulfilled, (state, action) => {
        state.loading = false;
        state.myPermissions = action.payload;
      })
      .addCase(fetchMyPermissions.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchMyRoles.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchMyRoles.fulfilled, (state, action) => {
        state.loading = false;
        state.myRoles = action.payload;
      })
      .addCase(fetchMyRoles.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      createRole,
      updateRole,
      cloneRole,
      syncRolePermissions,
      syncPermissionsCatalog,
      assignUserRole,
      removeUserRole,
      checkPermission,
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
      .addCase(createRole.fulfilled, (state, action) => {
        state.saving = false;
        state.roles = upsertItem(state.roles, action.payload);
      })
      .addCase(updateRole.fulfilled, (state, action) => {
        state.saving = false;
        state.roles = upsertItem(state.roles, action.payload);
      })
      .addCase(cloneRole.fulfilled, (state, action) => {
        state.saving = false;
        state.roles = upsertItem(state.roles, action.payload);
      })
      .addCase(syncRolePermissions.fulfilled, (state, action) => {
        state.saving = false;
        state.roles = upsertItem(state.roles, action.payload);
      })
      .addCase(syncPermissionsCatalog.fulfilled, (state, action) => {
        state.saving = false;
        state.permissions = action.payload;
      })
      .addCase(assignUserRole.fulfilled, (state, action) => {
        state.saving = false;
        state.selectedUserId = String(action.payload.userId);
        state.selectedUserRoles = upsertItem(state.selectedUserRoles, action.payload.assignment);
      })
      .addCase(removeUserRole.fulfilled, (state, action) => {
        state.saving = false;
        state.selectedUserRoles = state.selectedUserRoles.filter((item) => item.role_id !== action.payload.roleId);
      })
      .addCase(checkPermission.fulfilled, (state, action) => {
        state.saving = false;
        state.lastPermissionCheck = action.payload;
      })
      .addCase(deleteRole.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(deleteRole.fulfilled, (state, action) => {
        state.saving = false;
        state.roles = state.roles.filter((item) => item.id !== action.payload.id);
      })
      .addCase(deleteRole.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export const { setSelectedUserId, clearPermissionCheck } = rbacSlice.actions;
export default rbacSlice.reducer;
