import { axiosClient } from '../../../api/axiosClient';

const basePath = '/payments';

export const paymentsApi = {
  getGateways: (params) => axiosClient.get(`${basePath}/gateways`, { params }),
  createGateway: (payload) => axiosClient.post(`${basePath}/gateways`, payload),
  updateGateway: (id, payload) => axiosClient.put(`${basePath}/gateways/${id}`, payload),
  deleteGateway: (id) => axiosClient.delete(`${basePath}/gateways/${id}`),

  initiatePayment: (payload) => axiosClient.post(`${basePath}/initiate`, payload),
  verifyPayment: (payload) => axiosClient.post(`${basePath}/verify`, payload),
  getTransactions: (params) => axiosClient.get(`${basePath}/transactions`, { params }),
  getTransaction: (id) => axiosClient.get(`${basePath}/transactions/${id}`),
  manualApproveTransaction: (id, payload) => axiosClient.post(`${basePath}/transactions/${id}/manual-approve`, payload),
  cancelTransaction: (id, payload) => axiosClient.post(`${basePath}/transactions/${id}/cancel`, payload),

  initiateUpi: (payload) => axiosClient.post(`${basePath}/upi/initiate`, payload),
  verifyUpi: (payload) => axiosClient.post(`${basePath}/upi/verify`, payload),
  getUpiRequest: (transactionId) => axiosClient.get(`${basePath}/upi/${transactionId}`),
  manualVerifyUpi: (transactionId, payload) => axiosClient.post(`${basePath}/upi/${transactionId}/manual-verify`, payload),
  expireUpi: (transactionId) => axiosClient.post(`${basePath}/upi/${transactionId}/expire`),

  requestRefund: (transactionId, payload) => axiosClient.post(`${basePath}/transactions/${transactionId}/refund`, payload),
  getRefunds: (params) => axiosClient.get(`${basePath}/refunds`, { params }),
  processRefund: (id) => axiosClient.post(`${basePath}/refunds/${id}/process`),

  getReconciliations: (params) => axiosClient.get(`${basePath}/reconciliations`, { params }),
  reconcileTransaction: (transactionId, payload) => axiosClient.post(`${basePath}/transactions/${transactionId}/reconcile`, payload),

  getPaymentSummary: (params) => axiosClient.get(`${basePath}/reports/payment-summary`, { params }),
  getUpiPaymentsReport: (params) => axiosClient.get(`${basePath}/reports/upipayments`, { params }),
  getFailedTransactionsReport: (params) => axiosClient.get(`${basePath}/reports/failed-transactions`, { params }),

  getStudents: (params) => axiosClient.get('/students', { params }),
  getTenants: (params) => axiosClient.get('/saas/tenants', { params }),
};
