import { axiosClient } from '../../../api/axiosClient';

const basePath = '/finance';

export const financeApi = {
  getFeeCategories: (params) => axiosClient.get(`${basePath}/fee-categories`, { params }),
  createFeeCategory: (payload) => axiosClient.post(`${basePath}/fee-categories`, payload),
  updateFeeCategory: (id, payload) => axiosClient.put(`${basePath}/fee-categories/${id}`, payload),
  deleteFeeCategory: (id) => axiosClient.delete(`${basePath}/fee-categories/${id}`),

  getExpenseCategories: (params) => axiosClient.get(`${basePath}/expense-categories`, { params }),
  createExpenseCategory: (payload) => axiosClient.post(`${basePath}/expense-categories`, payload),
  updateExpenseCategory: (id, payload) => axiosClient.put(`${basePath}/expense-categories/${id}`, payload),
  deleteExpenseCategory: (id) => axiosClient.delete(`${basePath}/expense-categories/${id}`),

  getFeeHeads: (params) => axiosClient.get(`${basePath}/fee-heads`, { params }),
  createFeeHead: (payload) => axiosClient.post(`${basePath}/fee-heads`, payload),
  updateFeeHead: (id, payload) => axiosClient.put(`${basePath}/fee-heads/${id}`, payload),
  deleteFeeHead: (id) => axiosClient.delete(`${basePath}/fee-heads/${id}`),

  getDiscountTypes: (params) => axiosClient.get(`${basePath}/discount-types`, { params }),
  createDiscountType: (payload) => axiosClient.post(`${basePath}/discount-types`, payload),
  updateDiscountType: (id, payload) => axiosClient.put(`${basePath}/discount-types/${id}`, payload),
  deleteDiscountType: (id) => axiosClient.delete(`${basePath}/discount-types/${id}`),

  getStudentDiscounts: (params) => axiosClient.get(`${basePath}/student-discounts`, { params }),
  createStudentDiscount: (payload) => axiosClient.post(`${basePath}/student-discounts`, payload),
  updateStudentDiscount: (id, payload) => axiosClient.put(`${basePath}/student-discounts/${id}`, payload),
  deleteStudentDiscount: (id) => axiosClient.delete(`${basePath}/student-discounts/${id}`),
  approveStudentDiscount: (id) => axiosClient.post(`${basePath}/student-discounts/${id}/approve`),
  rejectStudentDiscount: (id) => axiosClient.post(`${basePath}/student-discounts/${id}/reject`),

  getFineRules: (params) => axiosClient.get(`${basePath}/fine-rules`, { params }),
  createFineRule: (payload) => axiosClient.post(`${basePath}/fine-rules`, payload),
  updateFineRule: (id, payload) => axiosClient.put(`${basePath}/fine-rules/${id}`, payload),
  deleteFineRule: (id) => axiosClient.delete(`${basePath}/fine-rules/${id}`),

  getFeeStructures: (params) => axiosClient.get(`${basePath}/fee-structures`, { params }),
  createFeeStructure: (payload) => axiosClient.post(`${basePath}/fee-structures`, payload),
  updateFeeStructure: (id, payload) => axiosClient.put(`${basePath}/fee-structures/${id}`, payload),
  deleteFeeStructure: (id) => axiosClient.delete(`${basePath}/fee-structures/${id}`),

  getStudentFeeAssignments: (params) => axiosClient.get(`${basePath}/student-fee-assignments`, { params }),
  createStudentFeeAssignment: (payload) => axiosClient.post(`${basePath}/student-fee-assignments`, payload),
  updateStudentFeeAssignment: (id, payload) => axiosClient.put(`${basePath}/student-fee-assignments/${id}`, payload),
  deleteStudentFeeAssignment: (id) => axiosClient.delete(`${basePath}/student-fee-assignments/${id}`),
  assignFeeStructureToStudent: (studentId, payload) => axiosClient.post(`${basePath}/students/${studentId}/assign-fee-structure`, payload),

  getFeeInstallments: (params) => axiosClient.get(`${basePath}/fee-installments`, { params }),
  createFeeInstallment: (payload) => axiosClient.post(`${basePath}/fee-installments`, payload),
  updateFeeInstallment: (id, payload) => axiosClient.put(`${basePath}/fee-installments/${id}`, payload),
  deleteFeeInstallment: (id) => axiosClient.delete(`${basePath}/fee-installments/${id}`),
  generateInstallmentsForAssignment: (assignmentId) => axiosClient.post(`${basePath}/student-fee-assignments/${assignmentId}/generate-installments`),

  getInvoices: (params) => axiosClient.get(`${basePath}/invoices`, { params }),
  createInvoice: (payload) => axiosClient.post(`${basePath}/invoices`, payload),
  updateInvoice: (id, payload) => axiosClient.put(`${basePath}/invoices/${id}`, payload),
  deleteInvoice: (id) => axiosClient.delete(`${basePath}/invoices/${id}`),
  issueInvoice: (id) => axiosClient.post(`${basePath}/invoices/${id}/issue`),
  cancelInvoice: (id) => axiosClient.post(`${basePath}/invoices/${id}/cancel`),
  applyDiscountToInvoice: (id, payload) => axiosClient.post(`${basePath}/invoices/${id}/apply-discount`, payload),
  applyFineToInvoice: (id, payload) => axiosClient.post(`${basePath}/invoices/${id}/apply-fine`, payload),

  getPayments: (params) => axiosClient.get(`${basePath}/payments`, { params }),
  collectPayment: (payload) => axiosClient.post(`${basePath}/payments/collect`, payload),
  updatePayment: (id, payload) => axiosClient.put(`${basePath}/payments/${id}`, payload),
  deletePayment: (id) => axiosClient.delete(`${basePath}/payments/${id}`),
  confirmPayment: (id, payload) => axiosClient.post(`${basePath}/payments/${id}/confirm`, payload),
  failPayment: (id, payload) => axiosClient.post(`${basePath}/payments/${id}/fail`, payload),

  getReceipts: (params) => axiosClient.get(`${basePath}/receipts`, { params }),
  getReceipt: (id) => axiosClient.get(`${basePath}/receipts/${id}`),
  downloadReceipt: (id) => axiosClient.get(`${basePath}/receipts/${id}/download`),

  getRefunds: (params) => axiosClient.get(`${basePath}/refunds`, { params }),
  createRefund: (payload) => axiosClient.post(`${basePath}/refunds`, payload),
  deleteRefund: (id) => axiosClient.delete(`${basePath}/refunds/${id}`),
  approveRefund: (id) => axiosClient.post(`${basePath}/refunds/${id}/approve`),
  processRefund: (id) => axiosClient.post(`${basePath}/refunds/${id}/process`),

  getExpenses: (params) => axiosClient.get(`${basePath}/expenses`, { params }),
  createExpense: (payload) => axiosClient.post(`${basePath}/expenses`, payload),
  updateExpense: (id, payload) => axiosClient.put(`${basePath}/expenses/${id}`, payload),
  deleteExpense: (id) => axiosClient.delete(`${basePath}/expenses/${id}`),
  approveExpense: (id) => axiosClient.post(`${basePath}/expenses/${id}/approve`),
  markExpensePaid: (id) => axiosClient.post(`${basePath}/expenses/${id}/mark-paid`),

  getLedgerAccounts: (params) => axiosClient.get(`${basePath}/ledger-accounts`, { params }),
  createLedgerAccount: (payload) => axiosClient.post(`${basePath}/ledger-accounts`, payload),
  updateLedgerAccount: (id, payload) => axiosClient.put(`${basePath}/ledger-accounts/${id}`, payload),
  deleteLedgerAccount: (id) => axiosClient.delete(`${basePath}/ledger-accounts/${id}`),

  getLedgerEntries: (params) => axiosClient.get(`${basePath}/ledger-entries`, { params }),
  createLedgerEntry: (payload) => axiosClient.post(`${basePath}/ledger-entries`, payload),
  updateLedgerEntry: (id, payload) => axiosClient.put(`${basePath}/ledger-entries/${id}`, payload),
  deleteLedgerEntry: (id) => axiosClient.delete(`${basePath}/ledger-entries/${id}`),

  getFeeCollectionReport: (params) => axiosClient.get(`${basePath}/reports/fee-collection`, { params }),
  getOutstandingFeesReport: (params) => axiosClient.get(`${basePath}/reports/outstanding-fees`, { params }),
  getStudentLedgerReport: (params) => axiosClient.get(`${basePath}/reports/student-ledger`, { params }),
  getDailyCollectionReport: (params) => axiosClient.get(`${basePath}/reports/daily-collection`, { params }),
  getExpenseSummaryReport: (params) => axiosClient.get(`${basePath}/reports/expense-summary`, { params }),
  getIncomeVsExpenseReport: (params) => axiosClient.get(`${basePath}/reports/income-vs-expense`, { params }),

  getAcademicYears: (params) => axiosClient.get('/academic-years', { params }),
  getClasses: (params) => axiosClient.get('/classes', { params }),
  getSections: (params) => axiosClient.get('/sections', { params }),
  getStudents: (params) => axiosClient.get('/students', { params }),
};
