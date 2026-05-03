import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { documentsApi } from '../services/documentsApi';

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

export const fetchDocumentCategories = createAsyncThunk('documents/fetchCategories', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getCategories(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load document categories.'));
  }
});

export const createDocumentCategory = createMutationThunk('documents/createCategory', documentsApi.createCategory, 'Failed to create document category.');
export const updateDocumentCategory = createUpdateThunk('documents/updateCategory', documentsApi.updateCategory, 'Failed to update document category.');
export const deleteDocumentCategory = createAsyncThunk('documents/deleteCategory', async (id, thunkApi) => {
  try {
    await documentsApi.deleteCategory(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete document category.'));
  }
});

export const fetchDocumentFolders = createAsyncThunk('documents/fetchFolders', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getFolders(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load document folders.'));
  }
});

export const createDocumentFolder = createMutationThunk('documents/createFolder', documentsApi.createFolder, 'Failed to create document folder.');
export const updateDocumentFolder = createUpdateThunk('documents/updateFolder', documentsApi.updateFolder, 'Failed to update document folder.');
export const deleteDocumentFolder = createAsyncThunk('documents/deleteFolder', async (id, thunkApi) => {
  try {
    await documentsApi.deleteFolder(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete document folder.'));
  }
});

export const fetchDocuments = createAsyncThunk('documents/fetchDocuments', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getDocuments(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load documents.'));
  }
});

export const fetchDocument = createAsyncThunk('documents/fetchDocument', async (id, thunkApi) => {
  try {
    const response = await documentsApi.getDocument(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load document details.'));
  }
});

export const createDocument = createMutationThunk('documents/createDocument', documentsApi.createDocument, 'Failed to upload document.');
export const updateDocument = createUpdateThunk('documents/updateDocument', documentsApi.updateDocument, 'Failed to update document.');
export const deleteDocument = createAsyncThunk('documents/deleteDocument', async (id, thunkApi) => {
  try {
    await documentsApi.deleteDocument(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to archive document.'));
  }
});
export const restoreDocument = createAsyncThunk('documents/restoreDocument', async (id, thunkApi) => {
  try {
    const response = await documentsApi.restoreDocument(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to restore document.'));
  }
});

export const fetchDocumentVersions = createAsyncThunk('documents/fetchVersions', async (id, thunkApi) => {
  try {
    const response = await documentsApi.getDocumentVersions(id);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load document versions.'));
  }
});

export const uploadDocumentVersion = createUpdateThunk('documents/uploadVersion', documentsApi.uploadDocumentVersion, 'Failed to upload a new document version.');

export const fetchDocumentAuditLogs = createAsyncThunk('documents/fetchAuditLogs', async (id, thunkApi) => {
  try {
    const response = await documentsApi.getDocumentAuditLogs(id);
    return normalizeCollection(response.data.data).items;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load document audit logs.'));
  }
});

export const fetchDocumentPermissions = createAsyncThunk('documents/fetchPermissions', async (id, thunkApi) => {
  try {
    const response = await documentsApi.getDocumentPermissions(id);
    return normalizeCollection(response.data.data).items;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load document permissions.'));
  }
});

export const createDocumentPermission = createAsyncThunk('documents/createPermission', async ({ id, payload }, thunkApi) => {
  try {
    const response = await documentsApi.createDocumentPermission(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to add document permission.'));
  }
});

export const updateDocumentPermission = createUpdateThunk('documents/updatePermission', documentsApi.updateDocumentPermission, 'Failed to update document permission.');
export const deleteDocumentPermission = createAsyncThunk('documents/deletePermission', async (id, thunkApi) => {
  try {
    await documentsApi.deleteDocumentPermission(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete document permission.'));
  }
});

export const fetchPendingDocumentVerifications = createAsyncThunk('documents/fetchPendingVerifications', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getPendingVerifications(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load verification queue.'));
  }
});

export const verifyDocument = createUpdateThunk('documents/verifyDocument', documentsApi.verifyDocument, 'Failed to verify document.');
export const rejectDocument = createUpdateThunk('documents/rejectDocument', documentsApi.rejectDocument, 'Failed to reject document.');

export const fetchDocumentTags = createAsyncThunk('documents/fetchTags', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getTags(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load document tags.'));
  }
});

export const createDocumentTag = createMutationThunk('documents/createTag', documentsApi.createTag, 'Failed to create document tag.');
export const deleteDocumentTag = createAsyncThunk('documents/deleteTag', async (id, thunkApi) => {
  try {
    await documentsApi.deleteTag(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete document tag.'));
  }
});

export const createDocumentBulkUpload = createMutationThunk('documents/createBulkUpload', documentsApi.createBulkUpload, 'Failed to start bulk upload.');
export const fetchDocumentBulkUpload = createAsyncThunk('documents/fetchBulkUpload', async (id, thunkApi) => {
  try {
    const response = await documentsApi.getBulkUpload(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load bulk upload status.'));
  }
});

export const fetchExpiringDocuments = createAsyncThunk('documents/fetchExpiringDocuments', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getExpiringDocuments(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load expiring documents.'));
  }
});

export const fetchExpiredDocuments = createAsyncThunk('documents/fetchExpiredDocuments', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getExpiredDocuments(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load expired documents.'));
  }
});

export const fetchDocumentVerificationStatusReport = createAsyncThunk('documents/fetchVerificationStatusReport', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getVerificationStatusReport(params);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load verification report.'));
  }
});

export const fetchDocumentStorageUsageReport = createAsyncThunk('documents/fetchStorageUsageReport', async (params = {}, thunkApi) => {
  try {
    const response = await documentsApi.getStorageUsageReport(params);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load storage usage report.'));
  }
});

const initialState = {
  categories: [],
  folders: [],
  documents: [],
  versions: [],
  permissions: [],
  pendingVerifications: [],
  tags: [],
  auditLogs: [],
  selectedDocument: null,
  selectedBulkUpload: null,
  reports: {
    expiring: [],
    expired: [],
    verificationStatus: null,
    storageUsage: null,
  },
  categoriesPagination: { page: 1, totalPages: 1, total: 0 },
  foldersPagination: { page: 1, totalPages: 1, total: 0 },
  documentsPagination: { page: 1, totalPages: 1, total: 0 },
  versionsPagination: { page: 1, totalPages: 1, total: 0 },
  verificationPagination: { page: 1, totalPages: 1, total: 0 },
  tagsPagination: { page: 1, totalPages: 1, total: 0 },
  expiringPagination: { page: 1, totalPages: 1, total: 0 },
  expiredPagination: { page: 1, totalPages: 1, total: 0 },
  loading: false,
  saving: false,
  error: null,
};

const documentsSlice = createSlice({
  name: 'documents',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    [
      [fetchDocumentCategories, 'categories', 'categoriesPagination'],
      [fetchDocumentFolders, 'folders', 'foldersPagination'],
      [fetchDocuments, 'documents', 'documentsPagination'],
      [fetchDocumentVersions, 'versions', 'versionsPagination'],
      [fetchPendingDocumentVerifications, 'pendingVerifications', 'verificationPagination'],
      [fetchDocumentTags, 'tags', 'tagsPagination'],
      [fetchExpiringDocuments, 'reports.expiring', 'expiringPagination'],
      [fetchExpiredDocuments, 'reports.expired', 'expiredPagination'],
    ].forEach(([thunk, key, paginationKey]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.loading = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.loading = false;
          if (key.includes('.')) {
            const [parent, child] = key.split('.');
            state[parent][child] = action.payload.items;
          } else {
            state[key] = action.payload.items;
          }
          state[paginationKey] = action.payload.pagination;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.loading = false;
          state.error = action.payload;
        });
    });

    [
      fetchDocument,
      fetchDocumentAuditLogs,
      fetchDocumentPermissions,
      fetchDocumentBulkUpload,
      fetchDocumentVerificationStatusReport,
      fetchDocumentStorageUsageReport,
    ].forEach((thunk) => {
      builder.addCase(thunk.pending, (state) => {
        state.loading = true;
        state.error = null;
      });
    });

    builder
      .addCase(fetchDocument.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedDocument = action.payload;
        state.documents = upsertItem(state.documents, action.payload);
      })
      .addCase(fetchDocumentAuditLogs.fulfilled, (state, action) => {
        state.loading = false;
        state.auditLogs = action.payload;
      })
      .addCase(fetchDocumentPermissions.fulfilled, (state, action) => {
        state.loading = false;
        state.permissions = action.payload;
      })
      .addCase(fetchDocumentBulkUpload.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedBulkUpload = action.payload;
      })
      .addCase(fetchDocumentVerificationStatusReport.fulfilled, (state, action) => {
        state.loading = false;
        state.reports.verificationStatus = action.payload;
      })
      .addCase(fetchDocumentStorageUsageReport.fulfilled, (state, action) => {
        state.loading = false;
        state.reports.storageUsage = action.payload;
      });

    [
      fetchDocument,
      fetchDocumentAuditLogs,
      fetchDocumentPermissions,
      fetchDocumentBulkUpload,
      fetchDocumentVerificationStatusReport,
      fetchDocumentStorageUsageReport,
    ].forEach((thunk) => {
      builder.addCase(thunk.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });
    });

    [
      [createDocumentCategory, 'categories'],
      [updateDocumentCategory, 'categories'],
      [createDocumentFolder, 'folders'],
      [updateDocumentFolder, 'folders'],
      [createDocument, 'documents'],
      [updateDocument, 'documents'],
      [restoreDocument, 'documents'],
      [uploadDocumentVersion, 'versions'],
      [createDocumentPermission, 'permissions'],
      [updateDocumentPermission, 'permissions'],
      [verifyDocument, 'pendingVerifications'],
      [rejectDocument, 'pendingVerifications'],
      [createDocumentTag, 'tags'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = upsertItem(state[key], action.payload);

          if (key === 'documents') {
            state.selectedDocument = action.payload;
          }

          if (key === 'versions') {
            state.selectedDocument = state.selectedDocument
              ? {
                  ...state.selectedDocument,
                  current_file: action.payload,
                }
              : state.selectedDocument;
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [
      [deleteDocumentCategory, 'categories'],
      [deleteDocumentFolder, 'folders'],
      [deleteDocument, 'documents'],
      [deleteDocumentPermission, 'permissions'],
      [deleteDocumentTag, 'tags'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = state[key].filter((item) => item.id !== action.payload);
          if (key === 'documents' && state.selectedDocument?.id === action.payload) {
            state.selectedDocument = null;
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(createDocumentBulkUpload.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(createDocumentBulkUpload.fulfilled, (state, action) => {
        state.saving = false;
        state.selectedBulkUpload = action.payload;
      })
      .addCase(createDocumentBulkUpload.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default documentsSlice.reducer;
