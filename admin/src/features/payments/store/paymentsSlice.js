import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { paymentsApi } from '../services/paymentsApi';

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

export const fetchPaymentReferenceData = createAsyncThunk(
  'payments/fetchReferenceData',
  async (_, thunkApi) => {
    try {
      const [students, tenants] = await Promise.all([
        paymentsApi.getStudents({ per_page: 100 }),
        paymentsApi.getTenants({ per_page: 100 }),
      ]);

      return {
        students: normalizeCollection(students.data.data).items,
        tenants: normalizeCollection(tenants.data.data).items,
      };
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, 'Failed to load payment reference data.'));
    }
  },
);

export const fetchPaymentGateways = createAsyncThunk('payments/fetchGateways', async (params = {}, thunkApi) => {
  try {
    const response = await paymentsApi.getGateways(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load payment gateways.'));
  }
});

export const createPaymentGateway = createMutationThunk('payments/createGateway', paymentsApi.createGateway, 'Failed to create payment gateway.');
export const updatePaymentGateway = createUpdateThunk('payments/updateGateway', paymentsApi.updateGateway, 'Failed to update payment gateway.');
export const deletePaymentGateway = createAsyncThunk('payments/deleteGateway', async (id, thunkApi) => {
  try {
    await paymentsApi.deleteGateway(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete payment gateway.'));
  }
});

export const fetchPaymentTransactions = createAsyncThunk('payments/fetchTransactions', async (params = {}, thunkApi) => {
  try {
    const response = await paymentsApi.getTransactions(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load payment transactions.'));
  }
});

export const fetchPaymentTransaction = createAsyncThunk('payments/fetchTransaction', async (id, thunkApi) => {
  try {
    const response = await paymentsApi.getTransaction(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load payment transaction.'));
  }
});

export const initiatePayment = createMutationThunk('payments/initiatePayment', paymentsApi.initiatePayment, 'Failed to initiate payment.');
export const verifyPayment = createMutationThunk('payments/verifyPayment', paymentsApi.verifyPayment, 'Failed to verify payment.');
export const manualApprovePayment = createUpdateThunk('payments/manualApprove', paymentsApi.manualApproveTransaction, 'Failed to update manual approval.');
export const cancelPayment = createUpdateThunk('payments/cancelPayment', paymentsApi.cancelTransaction, 'Failed to cancel payment.');

export const initiateUpiPayment = createMutationThunk('payments/initiateUpiPayment', paymentsApi.initiateUpi, 'Failed to initiate UPI payment.');
export const verifyUpiPayment = createMutationThunk('payments/verifyUpiPayment', paymentsApi.verifyUpi, 'Failed to submit UPI verification.');
export const fetchUpiPaymentRequest = createAsyncThunk('payments/fetchUpiRequest', async (transactionId, thunkApi) => {
  try {
    const response = await paymentsApi.getUpiRequest(transactionId);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load UPI payment request.'));
  }
});
export const manualVerifyUpiPayment = createUpdateThunk('payments/manualVerifyUpi', paymentsApi.manualVerifyUpi, 'Failed to manually verify UPI payment.');
export const expireUpiPayment = createAsyncThunk('payments/expireUpi', async (transactionId, thunkApi) => {
  try {
    const response = await paymentsApi.expireUpi(transactionId);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to expire UPI payment request.'));
  }
});

export const fetchPaymentRefunds = createAsyncThunk('payments/fetchRefunds', async (params = {}, thunkApi) => {
  try {
    const response = await paymentsApi.getRefunds(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load refunds.'));
  }
});
export const requestPaymentRefund = createUpdateThunk('payments/requestRefund', paymentsApi.requestRefund, 'Failed to request refund.');
export const processPaymentRefund = createAsyncThunk('payments/processRefund', async (id, thunkApi) => {
  try {
    const response = await paymentsApi.processRefund(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to process refund.'));
  }
});

export const fetchPaymentReconciliations = createAsyncThunk('payments/fetchReconciliations', async (params = {}, thunkApi) => {
  try {
    const response = await paymentsApi.getReconciliations(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load reconciliation logs.'));
  }
});
export const reconcilePaymentTransaction = createUpdateThunk('payments/reconcile', paymentsApi.reconcileTransaction, 'Failed to reconcile payment.');

export const fetchPaymentSummary = createAsyncThunk('payments/fetchSummary', async (params = {}, thunkApi) => {
  try {
    const response = await paymentsApi.getPaymentSummary(params);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load payment summary.'));
  }
});

export const fetchUpiPaymentsReport = createAsyncThunk('payments/fetchUpiReport', async (params = {}, thunkApi) => {
  try {
    const response = await paymentsApi.getUpiPaymentsReport(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load UPI report.'));
  }
});

export const fetchFailedTransactionsReport = createAsyncThunk('payments/fetchFailedReport', async (params = {}, thunkApi) => {
  try {
    const response = await paymentsApi.getFailedTransactionsReport(params);
    return normalizeCollection(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load failed transaction report.'));
  }
});

const initialState = {
  gateways: [],
  transactions: [],
  refunds: [],
  reconciliations: [],
  selectedTransaction: null,
  selectedUpiRequest: null,
  reports: {
    summary: null,
    upiPayments: [],
    failedTransactions: [],
  },
  referenceData: {
    students: [],
    tenants: [],
  },
  gatewaysPagination: { page: 1, totalPages: 1, total: 0 },
  transactionsPagination: { page: 1, totalPages: 1, total: 0 },
  refundsPagination: { page: 1, totalPages: 1, total: 0 },
  reconciliationsPagination: { page: 1, totalPages: 1, total: 0 },
  upiReportPagination: { page: 1, totalPages: 1, total: 0 },
  failedReportPagination: { page: 1, totalPages: 1, total: 0 },
  lastInitiatedPayment: null,
  loading: false,
  saving: false,
  error: null,
};

const paymentsSlice = createSlice({
  name: 'payments',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    [
      [fetchPaymentGateways, 'gateways', 'gatewaysPagination'],
      [fetchPaymentTransactions, 'transactions', 'transactionsPagination'],
      [fetchPaymentRefunds, 'refunds', 'refundsPagination'],
      [fetchPaymentReconciliations, 'reconciliations', 'reconciliationsPagination'],
      [fetchUpiPaymentsReport, 'reports.upiPayments', 'upiReportPagination'],
      [fetchFailedTransactionsReport, 'reports.failedTransactions', 'failedReportPagination'],
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

    builder
      .addCase(fetchPaymentReferenceData.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPaymentReferenceData.fulfilled, (state, action) => {
        state.loading = false;
        state.referenceData = action.payload;
      })
      .addCase(fetchPaymentReferenceData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchPaymentTransaction.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPaymentTransaction.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedTransaction = action.payload;
        state.transactions = upsertItem(state.transactions, action.payload);
      })
      .addCase(fetchPaymentTransaction.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchUpiPaymentRequest.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchUpiPaymentRequest.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedUpiRequest = action.payload;
      })
      .addCase(fetchUpiPaymentRequest.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchPaymentSummary.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPaymentSummary.fulfilled, (state, action) => {
        state.loading = false;
        state.reports.summary = action.payload;
      })
      .addCase(fetchPaymentSummary.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [createPaymentGateway, 'gateways'],
      [updatePaymentGateway, 'gateways'],
      [verifyPayment, 'transactions'],
      [manualApprovePayment, 'transactions'],
      [cancelPayment, 'transactions'],
      [requestPaymentRefund, 'refunds'],
      [processPaymentRefund, 'refunds'],
      [reconcilePaymentTransaction, 'reconciliations'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key] = upsertItem(state[key], action.payload);

          if (key === 'transactions') {
            state.selectedTransaction = action.payload;
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(deletePaymentGateway.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(deletePaymentGateway.fulfilled, (state, action) => {
        state.saving = false;
        state.gateways = state.gateways.filter((item) => item.id !== action.payload);
      })
      .addCase(deletePaymentGateway.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(initiatePayment.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(initiatePayment.fulfilled, (state, action) => {
        state.saving = false;
        state.lastInitiatedPayment = action.payload;
        state.transactions = upsertItem(state.transactions, action.payload.transaction);
      })
      .addCase(initiatePayment.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(initiateUpiPayment.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(initiateUpiPayment.fulfilled, (state, action) => {
        state.saving = false;
        state.lastInitiatedPayment = action.payload;
        state.transactions = upsertItem(state.transactions, action.payload.transaction);
        state.selectedUpiRequest = action.payload.upi_request;
      })
      .addCase(initiateUpiPayment.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(verifyUpiPayment.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(verifyUpiPayment.fulfilled, (state, action) => {
        state.saving = false;
        state.transactions = upsertItem(state.transactions, action.payload);
        state.selectedTransaction = action.payload;
      })
      .addCase(verifyUpiPayment.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(manualVerifyUpiPayment.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(manualVerifyUpiPayment.fulfilled, (state, action) => {
        state.saving = false;
        state.transactions = upsertItem(state.transactions, action.payload);
        state.selectedTransaction = action.payload;
      })
      .addCase(manualVerifyUpiPayment.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(expireUpiPayment.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(expireUpiPayment.fulfilled, (state, action) => {
        state.saving = false;
        state.selectedUpiRequest = action.payload;
      })
      .addCase(expireUpiPayment.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default paymentsSlice.reducer;
