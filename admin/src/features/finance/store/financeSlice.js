import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { financeApi } from '../services/financeApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function resourceThunk(type, request, fallbackMessage) {
  return createAsyncThunk(type, async (payload, thunkApi) => {
    try {
      const response = await request(payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallbackMessage));
    }
  });
}

export const fetchFinanceMasterData = createAsyncThunk('finance/fetchFinanceMasterData', async (_, thunkApi) => {
  try {
    const [
      feeCategories,
      expenseCategories,
      feeHeads,
      discountTypes,
      fineRules,
      ledgerAccounts,
      feeStructures,
      academicYears,
      classes,
      sections,
      students,
    ] = await Promise.all([
      financeApi.getFeeCategories(),
      financeApi.getExpenseCategories(),
      financeApi.getFeeHeads(),
      financeApi.getDiscountTypes(),
      financeApi.getFineRules(),
      financeApi.getLedgerAccounts(),
      financeApi.getFeeStructures(),
      financeApi.getAcademicYears(),
      financeApi.getClasses(),
      financeApi.getSections(),
      financeApi.getStudents({ per_page: 100 }),
    ]);

    return {
      feeCategories: feeCategories.data.data || [],
      expenseCategories: expenseCategories.data.data || [],
      feeHeads: feeHeads.data.data || [],
      discountTypes: discountTypes.data.data || [],
      fineRules: fineRules.data.data || [],
      ledgerAccounts: ledgerAccounts.data.data || [],
      feeStructures: feeStructures.data.data || [],
      academicYears: academicYears.data.data || [],
      schoolClasses: classes.data.data || [],
      sections: sections.data.data || [],
      students: students.data.data || [],
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load finance master data.'));
  }
});

export const fetchFeeCategories = createAsyncThunk('finance/fetchFeeCategories', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getFeeCategories(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load fee categories.'));
  }
});

export const createFeeCategory = resourceThunk('finance/createFeeCategory', financeApi.createFeeCategory, 'Failed to create fee category.');

export const updateFeeCategory = createAsyncThunk('finance/updateFeeCategory', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateFeeCategory(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update fee category.'));
  }
});

export const deleteFeeCategory = createAsyncThunk('finance/deleteFeeCategory', async (id, thunkApi) => {
  try {
    await financeApi.deleteFeeCategory(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete fee category.'));
  }
});

export const fetchExpenseCategories = createAsyncThunk('finance/fetchExpenseCategories', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getExpenseCategories(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load expense categories.'));
  }
});

export const createExpenseCategory = resourceThunk('finance/createExpenseCategory', financeApi.createExpenseCategory, 'Failed to create expense category.');

export const updateExpenseCategory = createAsyncThunk('finance/updateExpenseCategory', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateExpenseCategory(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update expense category.'));
  }
});

export const deleteExpenseCategory = createAsyncThunk('finance/deleteExpenseCategory', async (id, thunkApi) => {
  try {
    await financeApi.deleteExpenseCategory(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete expense category.'));
  }
});

export const fetchFeeHeads = createAsyncThunk('finance/fetchFeeHeads', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getFeeHeads(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load fee heads.'));
  }
});

export const createFeeHead = resourceThunk('finance/createFeeHead', financeApi.createFeeHead, 'Failed to create fee head.');

export const updateFeeHead = createAsyncThunk('finance/updateFeeHead', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateFeeHead(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update fee head.'));
  }
});

export const deleteFeeHead = createAsyncThunk('finance/deleteFeeHead', async (id, thunkApi) => {
  try {
    await financeApi.deleteFeeHead(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete fee head.'));
  }
});

export const fetchDiscountTypes = createAsyncThunk('finance/fetchDiscountTypes', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getDiscountTypes(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load discount types.'));
  }
});

export const createDiscountType = resourceThunk('finance/createDiscountType', financeApi.createDiscountType, 'Failed to create discount type.');

export const updateDiscountType = createAsyncThunk('finance/updateDiscountType', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateDiscountType(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update discount type.'));
  }
});

export const deleteDiscountType = createAsyncThunk('finance/deleteDiscountType', async (id, thunkApi) => {
  try {
    await financeApi.deleteDiscountType(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete discount type.'));
  }
});

export const fetchStudentDiscounts = createAsyncThunk('finance/fetchStudentDiscounts', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getStudentDiscounts(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load student discounts.'));
  }
});

export const createStudentDiscount = resourceThunk('finance/createStudentDiscount', financeApi.createStudentDiscount, 'Failed to create student discount.');

export const updateStudentDiscount = createAsyncThunk('finance/updateStudentDiscount', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateStudentDiscount(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update student discount.'));
  }
});

export const deleteStudentDiscount = createAsyncThunk('finance/deleteStudentDiscount', async (id, thunkApi) => {
  try {
    await financeApi.deleteStudentDiscount(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete student discount.'));
  }
});

export const approveStudentDiscount = createAsyncThunk('finance/approveStudentDiscount', async (id, thunkApi) => {
  try {
    const response = await financeApi.approveStudentDiscount(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to approve student discount.'));
  }
});

export const rejectStudentDiscount = createAsyncThunk('finance/rejectStudentDiscount', async (id, thunkApi) => {
  try {
    const response = await financeApi.rejectStudentDiscount(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to reject student discount.'));
  }
});

export const fetchFineRules = createAsyncThunk('finance/fetchFineRules', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getFineRules(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load fine rules.'));
  }
});

export const createFineRule = resourceThunk('finance/createFineRule', financeApi.createFineRule, 'Failed to create fine rule.');

export const updateFineRule = createAsyncThunk('finance/updateFineRule', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateFineRule(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update fine rule.'));
  }
});

export const deleteFineRule = createAsyncThunk('finance/deleteFineRule', async (id, thunkApi) => {
  try {
    await financeApi.deleteFineRule(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete fine rule.'));
  }
});

export const fetchFeeStructures = createAsyncThunk('finance/fetchFeeStructures', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getFeeStructures(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load fee structures.'));
  }
});

export const createFeeStructure = resourceThunk('finance/createFeeStructure', financeApi.createFeeStructure, 'Failed to create fee structure.');

export const updateFeeStructure = createAsyncThunk('finance/updateFeeStructure', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateFeeStructure(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update fee structure.'));
  }
});

export const deleteFeeStructure = createAsyncThunk('finance/deleteFeeStructure', async (id, thunkApi) => {
  try {
    await financeApi.deleteFeeStructure(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete fee structure.'));
  }
});

export const fetchStudentFeeAssignments = createAsyncThunk('finance/fetchStudentFeeAssignments', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getStudentFeeAssignments(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load student fee assignments.'));
  }
});

export const createStudentFeeAssignment = resourceThunk('finance/createStudentFeeAssignment', financeApi.createStudentFeeAssignment, 'Failed to create student fee assignment.');

export const updateStudentFeeAssignment = createAsyncThunk('finance/updateStudentFeeAssignment', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateStudentFeeAssignment(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update student fee assignment.'));
  }
});

export const deleteStudentFeeAssignment = createAsyncThunk('finance/deleteStudentFeeAssignment', async (id, thunkApi) => {
  try {
    await financeApi.deleteStudentFeeAssignment(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete student fee assignment.'));
  }
});

export const assignFeeStructureToStudent = createAsyncThunk('finance/assignFeeStructureToStudent', async ({ studentId, payload }, thunkApi) => {
  try {
    const response = await financeApi.assignFeeStructureToStudent(studentId, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to assign fee structure to student.'));
  }
});

export const fetchFeeInstallments = createAsyncThunk('finance/fetchFeeInstallments', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getFeeInstallments(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load fee installments.'));
  }
});

export const createFeeInstallment = resourceThunk('finance/createFeeInstallment', financeApi.createFeeInstallment, 'Failed to create fee installment.');

export const updateFeeInstallment = createAsyncThunk('finance/updateFeeInstallment', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateFeeInstallment(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update fee installment.'));
  }
});

export const deleteFeeInstallment = createAsyncThunk('finance/deleteFeeInstallment', async (id, thunkApi) => {
  try {
    await financeApi.deleteFeeInstallment(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete fee installment.'));
  }
});

export const generateInstallmentsForAssignment = createAsyncThunk('finance/generateInstallmentsForAssignment', async (assignmentId, thunkApi) => {
  try {
    const response = await financeApi.generateInstallmentsForAssignment(assignmentId);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to generate installments.'));
  }
});

export const fetchInvoices = createAsyncThunk('finance/fetchInvoices', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getInvoices(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load invoices.'));
  }
});

export const createInvoice = resourceThunk('finance/createInvoice', financeApi.createInvoice, 'Failed to create invoice.');

export const updateInvoice = createAsyncThunk('finance/updateInvoice', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateInvoice(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update invoice.'));
  }
});

export const deleteInvoice = createAsyncThunk('finance/deleteInvoice', async (id, thunkApi) => {
  try {
    await financeApi.deleteInvoice(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete invoice.'));
  }
});

export const issueInvoice = createAsyncThunk('finance/issueInvoice', async (id, thunkApi) => {
  try {
    const response = await financeApi.issueInvoice(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to issue invoice.'));
  }
});

export const cancelInvoice = createAsyncThunk('finance/cancelInvoice', async (id, thunkApi) => {
  try {
    const response = await financeApi.cancelInvoice(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to cancel invoice.'));
  }
});

export const applyDiscountToInvoice = createAsyncThunk('finance/applyDiscountToInvoice', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.applyDiscountToInvoice(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to apply discount to invoice.'));
  }
});

export const applyFineToInvoice = createAsyncThunk('finance/applyFineToInvoice', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.applyFineToInvoice(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to apply fine to invoice.'));
  }
});

export const fetchPayments = createAsyncThunk('finance/fetchPayments', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getPayments(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load payments.'));
  }
});

export const collectPayment = resourceThunk('finance/collectPayment', financeApi.collectPayment, 'Failed to collect payment.');

export const updatePayment = createAsyncThunk('finance/updatePayment', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updatePayment(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update payment.'));
  }
});

export const deletePayment = createAsyncThunk('finance/deletePayment', async (id, thunkApi) => {
  try {
    await financeApi.deletePayment(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete payment.'));
  }
});

export const confirmPayment = createAsyncThunk('finance/confirmPayment', async ({ id, payload = {} }, thunkApi) => {
  try {
    const response = await financeApi.confirmPayment(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to confirm payment.'));
  }
});

export const failPayment = createAsyncThunk('finance/failPayment', async ({ id, payload = {} }, thunkApi) => {
  try {
    const response = await financeApi.failPayment(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark payment as failed.'));
  }
});

export const fetchReceipts = createAsyncThunk('finance/fetchReceipts', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getReceipts(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load receipts.'));
  }
});

export const fetchRefunds = createAsyncThunk('finance/fetchRefunds', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getRefunds(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load refunds.'));
  }
});

export const createRefund = resourceThunk('finance/createRefund', financeApi.createRefund, 'Failed to create refund request.');

export const deleteRefund = createAsyncThunk('finance/deleteRefund', async (id, thunkApi) => {
  try {
    await financeApi.deleteRefund(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete refund.'));
  }
});

export const approveRefund = createAsyncThunk('finance/approveRefund', async (id, thunkApi) => {
  try {
    const response = await financeApi.approveRefund(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to approve refund.'));
  }
});

export const processRefund = createAsyncThunk('finance/processRefund', async (id, thunkApi) => {
  try {
    const response = await financeApi.processRefund(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to process refund.'));
  }
});

export const fetchExpenses = createAsyncThunk('finance/fetchExpenses', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getExpenses(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load expenses.'));
  }
});

export const createExpense = resourceThunk('finance/createExpense', financeApi.createExpense, 'Failed to create expense.');

export const updateExpense = createAsyncThunk('finance/updateExpense', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateExpense(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update expense.'));
  }
});

export const deleteExpense = createAsyncThunk('finance/deleteExpense', async (id, thunkApi) => {
  try {
    await financeApi.deleteExpense(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete expense.'));
  }
});

export const approveExpense = createAsyncThunk('finance/approveExpense', async (id, thunkApi) => {
  try {
    const response = await financeApi.approveExpense(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to approve expense.'));
  }
});

export const markExpensePaid = createAsyncThunk('finance/markExpensePaid', async (id, thunkApi) => {
  try {
    const response = await financeApi.markExpensePaid(id);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to mark expense as paid.'));
  }
});

export const fetchLedgerAccounts = createAsyncThunk('finance/fetchLedgerAccounts', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getLedgerAccounts(params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load ledger accounts.'));
  }
});

export const createLedgerAccount = resourceThunk('finance/createLedgerAccount', financeApi.createLedgerAccount, 'Failed to create ledger account.');

export const updateLedgerAccount = createAsyncThunk('finance/updateLedgerAccount', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateLedgerAccount(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update ledger account.'));
  }
});

export const deleteLedgerAccount = createAsyncThunk('finance/deleteLedgerAccount', async (id, thunkApi) => {
  try {
    await financeApi.deleteLedgerAccount(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete ledger account.'));
  }
});

export const fetchLedgerEntries = createAsyncThunk('finance/fetchLedgerEntries', async (params = {}, thunkApi) => {
  try {
    const response = await financeApi.getLedgerEntries(params);
    return response.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load ledger entries.'));
  }
});

export const createLedgerEntry = resourceThunk('finance/createLedgerEntry', financeApi.createLedgerEntry, 'Failed to create ledger entry.');

export const updateLedgerEntry = createAsyncThunk('finance/updateLedgerEntry', async ({ id, payload }, thunkApi) => {
  try {
    const response = await financeApi.updateLedgerEntry(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update ledger entry.'));
  }
});

export const deleteLedgerEntry = createAsyncThunk('finance/deleteLedgerEntry', async (id, thunkApi) => {
  try {
    await financeApi.deleteLedgerEntry(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete ledger entry.'));
  }
});

export const fetchFinanceReports = createAsyncThunk('finance/fetchFinanceReports', async (params = {}, thunkApi) => {
  try {
    const [feeCollection, outstandingFees, studentLedger, dailyCollection, expenseSummary, incomeVsExpense] = await Promise.all([
      financeApi.getFeeCollectionReport(params),
      financeApi.getOutstandingFeesReport(params),
      financeApi.getStudentLedgerReport(params),
      financeApi.getDailyCollectionReport(params),
      financeApi.getExpenseSummaryReport(params),
      financeApi.getIncomeVsExpenseReport(params),
    ]);

    return {
      feeCollection: feeCollection.data.data,
      outstandingFees: outstandingFees.data.data,
      studentLedger: studentLedger.data.data,
      dailyCollection: dailyCollection.data.data,
      expenseSummary: expenseSummary.data.data,
      incomeVsExpense: incomeVsExpense.data.data,
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load finance reports.'));
  }
});

const initialState = {
  feeCategories: [],
  expenseCategories: [],
  feeHeads: [],
  discountTypes: [],
  studentDiscounts: [],
  fineRules: [],
  expenses: [],
  ledgerAccounts: [],
  ledgerEntries: [],
  feeStructures: [],
  studentFeeAssignments: [],
  feeInstallments: [],
  invoices: [],
  payments: [],
  receipts: [],
  refunds: [],
  students: [],
  academicYears: [],
  schoolClasses: [],
  sections: [],
  reports: {
    feeCollection: null,
    outstandingFees: null,
    studentLedger: null,
    dailyCollection: null,
    expenseSummary: null,
    incomeVsExpense: null,
  },
  loading: false,
  saving: false,
  error: null,
  assignmentsPagination: { page: 1, totalPages: 1, total: 0 },
  installmentsPagination: { page: 1, totalPages: 1, total: 0 },
  invoicesPagination: { page: 1, totalPages: 1, total: 0 },
  paymentsPagination: { page: 1, totalPages: 1, total: 0 },
  receiptsPagination: { page: 1, totalPages: 1, total: 0 },
  studentDiscountsPagination: { page: 1, totalPages: 1, total: 0 },
  refundsPagination: { page: 1, totalPages: 1, total: 0 },
  expensesPagination: { page: 1, totalPages: 1, total: 0 },
  ledgerEntriesPagination: { page: 1, totalPages: 1, total: 0 },
};

function upsertItem(items, payload) {
  const existing = items.some((item) => item.id === payload.id);
  if (!existing) {
    return [payload, ...items];
  }

  return items.map((item) => (item.id === payload.id ? payload : item));
}

const financeSlice = createSlice({
  name: 'finance',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchFinanceMasterData.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchFinanceMasterData.fulfilled, (state, action) => {
        state.loading = false;
        state.feeCategories = action.payload.feeCategories;
        state.expenseCategories = action.payload.expenseCategories;
        state.feeHeads = action.payload.feeHeads;
        state.discountTypes = action.payload.discountTypes;
        state.fineRules = action.payload.fineRules;
        state.ledgerAccounts = action.payload.ledgerAccounts;
        state.feeStructures = action.payload.feeStructures;
        state.academicYears = action.payload.academicYears;
        state.schoolClasses = action.payload.schoolClasses;
        state.sections = action.payload.sections;
        state.students = action.payload.students;
      })
      .addCase(fetchFinanceMasterData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchFinanceReports.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchFinanceReports.fulfilled, (state, action) => {
        state.loading = false;
        state.reports = action.payload;
      })
      .addCase(fetchFinanceReports.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [fetchFeeCategories, 'feeCategories'],
      [fetchExpenseCategories, 'expenseCategories'],
      [fetchFeeHeads, 'feeHeads'],
      [fetchDiscountTypes, 'discountTypes'],
      [fetchFineRules, 'fineRules'],
      [fetchFeeStructures, 'feeStructures'],
      [fetchLedgerAccounts, 'ledgerAccounts'],
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
      [createFeeCategory, 'feeCategories'],
      [createExpenseCategory, 'expenseCategories'],
      [createFeeHead, 'feeHeads'],
      [createDiscountType, 'discountTypes'],
      [createFineRule, 'fineRules'],
      [createFeeStructure, 'feeStructures'],
      [createLedgerAccount, 'ledgerAccounts'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key].unshift(action.payload);
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [
      [updateFeeCategory, 'feeCategories'],
      [updateExpenseCategory, 'expenseCategories'],
      [updateFeeHead, 'feeHeads'],
      [updateDiscountType, 'discountTypes'],
      [updateFineRule, 'fineRules'],
      [updateFeeStructure, 'feeStructures'],
      [updateLedgerAccount, 'ledgerAccounts'],
    ].forEach(([thunk, key]) => {
      builder.addCase(thunk.fulfilled, (state, action) => {
        state.saving = false;
        state[key] = upsertItem(state[key], action.payload);
      });
    });

    [
      [deleteFeeCategory, 'feeCategories'],
      [deleteExpenseCategory, 'expenseCategories'],
      [deleteFeeHead, 'feeHeads'],
      [deleteDiscountType, 'discountTypes'],
      [deleteFineRule, 'fineRules'],
      [deleteFeeStructure, 'feeStructures'],
      [deleteLedgerAccount, 'ledgerAccounts'],
    ].forEach(([thunk, key]) => {
      builder.addCase(thunk.fulfilled, (state, action) => {
        state.saving = false;
        state[key] = state[key].filter((item) => item.id !== action.payload);
      });
    });

    builder
      .addCase(fetchStudentFeeAssignments.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchStudentFeeAssignments.fulfilled, (state, action) => {
        state.loading = false;
        state.studentFeeAssignments = action.payload.data || [];
        state.assignmentsPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchStudentFeeAssignments.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchFeeInstallments.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchFeeInstallments.fulfilled, (state, action) => {
        state.loading = false;
        state.feeInstallments = action.payload.data || [];
        state.installmentsPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchFeeInstallments.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchInvoices.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchInvoices.fulfilled, (state, action) => {
        state.loading = false;
        state.invoices = action.payload.data || [];
        state.invoicesPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchInvoices.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchPayments.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchPayments.fulfilled, (state, action) => {
        state.loading = false;
        state.payments = action.payload.data || [];
        state.paymentsPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchPayments.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchReceipts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchReceipts.fulfilled, (state, action) => {
        state.loading = false;
        state.receipts = action.payload.data || [];
        state.receiptsPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchReceipts.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchStudentDiscounts.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchStudentDiscounts.fulfilled, (state, action) => {
        state.loading = false;
        state.studentDiscounts = action.payload.data || [];
        state.studentDiscountsPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchStudentDiscounts.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchRefunds.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchRefunds.fulfilled, (state, action) => {
        state.loading = false;
        state.refunds = action.payload.data || [];
        state.refundsPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchRefunds.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchExpenses.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchExpenses.fulfilled, (state, action) => {
        state.loading = false;
        state.expenses = action.payload.data || [];
        state.expensesPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchExpenses.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchLedgerEntries.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchLedgerEntries.fulfilled, (state, action) => {
        state.loading = false;
        state.ledgerEntries = action.payload.data || [];
        state.ledgerEntriesPagination = {
          page: action.payload.meta?.current_page || 1,
          totalPages: action.payload.meta?.last_page || 1,
          total: action.payload.meta?.total || 0,
        };
      })
      .addCase(fetchLedgerEntries.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      createStudentDiscount,
      updateStudentDiscount,
      approveStudentDiscount,
      rejectStudentDiscount,
      createStudentFeeAssignment,
      updateStudentFeeAssignment,
      assignFeeStructureToStudent,
      createFeeInstallment,
      updateFeeInstallment,
      createInvoice,
      updateInvoice,
      issueInvoice,
      cancelInvoice,
      applyDiscountToInvoice,
      applyFineToInvoice,
      collectPayment,
      updatePayment,
      confirmPayment,
      failPayment,
      createRefund,
      approveRefund,
      processRefund,
      createExpense,
      updateExpense,
      approveExpense,
      markExpensePaid,
      createLedgerEntry,
      updateLedgerEntry,
    ].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;

          if (action.type.startsWith('finance/createStudentDiscount') || action.type.startsWith('finance/updateStudentDiscount') || action.type.startsWith('finance/approveStudentDiscount') || action.type.startsWith('finance/rejectStudentDiscount')) {
            state.studentDiscounts = upsertItem(state.studentDiscounts, action.payload);
          }

          if (action.type.startsWith('finance/createStudentFeeAssignment') || action.type.startsWith('finance/updateStudentFeeAssignment') || action.type.startsWith('finance/assignFeeStructureToStudent')) {
            state.studentFeeAssignments = upsertItem(state.studentFeeAssignments, action.payload);
          }

          if (action.type.startsWith('finance/createFeeInstallment') || action.type.startsWith('finance/updateFeeInstallment')) {
            state.feeInstallments = upsertItem(state.feeInstallments, action.payload);
          }

          if (action.type.startsWith('finance/createInvoice') || action.type.startsWith('finance/updateInvoice') || action.type.startsWith('finance/issueInvoice') || action.type.startsWith('finance/cancelInvoice') || action.type.startsWith('finance/applyDiscountToInvoice') || action.type.startsWith('finance/applyFineToInvoice')) {
            state.invoices = upsertItem(state.invoices, action.payload);
          }

          if (action.type.startsWith('finance/collectPayment') || action.type.startsWith('finance/updatePayment') || action.type.startsWith('finance/confirmPayment') || action.type.startsWith('finance/failPayment')) {
            state.payments = upsertItem(state.payments, action.payload);
            if (action.payload.receipt) {
              state.receipts = upsertItem(state.receipts, action.payload.receipt);
            }
          }

          if (action.type.startsWith('finance/createRefund') || action.type.startsWith('finance/approveRefund') || action.type.startsWith('finance/processRefund')) {
            state.refunds = upsertItem(state.refunds, action.payload);
          }

          if (action.type.startsWith('finance/createExpense') || action.type.startsWith('finance/updateExpense') || action.type.startsWith('finance/approveExpense') || action.type.startsWith('finance/markExpensePaid')) {
            state.expenses = upsertItem(state.expenses, action.payload);
          }

          if (action.type.startsWith('finance/createLedgerEntry') || action.type.startsWith('finance/updateLedgerEntry')) {
            state.ledgerEntries = upsertItem(state.ledgerEntries, action.payload);
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    builder
      .addCase(deleteStudentFeeAssignment.fulfilled, (state, action) => {
        state.saving = false;
        state.studentFeeAssignments = state.studentFeeAssignments.filter((item) => item.id !== action.payload);
      })
      .addCase(generateInstallmentsForAssignment.fulfilled, (state, action) => {
        state.saving = false;
        state.feeInstallments = [
          ...action.payload,
          ...state.feeInstallments.filter((item) => !action.payload.some((generated) => generated.id === item.id)),
        ];
      })
      .addCase(deleteFeeInstallment.fulfilled, (state, action) => {
        state.saving = false;
        state.feeInstallments = state.feeInstallments.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteInvoice.fulfilled, (state, action) => {
        state.saving = false;
        state.invoices = state.invoices.filter((item) => item.id !== action.payload);
      })
      .addCase(deletePayment.fulfilled, (state, action) => {
        state.saving = false;
        state.payments = state.payments.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteStudentDiscount.fulfilled, (state, action) => {
        state.saving = false;
        state.studentDiscounts = state.studentDiscounts.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteRefund.fulfilled, (state, action) => {
        state.saving = false;
        state.refunds = state.refunds.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteExpense.fulfilled, (state, action) => {
        state.saving = false;
        state.expenses = state.expenses.filter((item) => item.id !== action.payload);
      })
      .addCase(deleteLedgerEntry.fulfilled, (state, action) => {
        state.saving = false;
        state.ledgerEntries = state.ledgerEntries.filter((item) => item.id !== action.payload);
      });
  },
});

export default financeSlice.reducer;
