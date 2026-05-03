import { axiosClient } from '../../../api/axiosClient';

const basePath = '/documents';

export const documentsApi = {
  getCategories: (params) => axiosClient.get(`${basePath}/categories`, { params }),
  createCategory: (payload) => axiosClient.post(`${basePath}/categories`, payload),
  updateCategory: (id, payload) => axiosClient.put(`${basePath}/categories/${id}`, payload),
  deleteCategory: (id) => axiosClient.delete(`${basePath}/categories/${id}`),

  getFolders: (params) => axiosClient.get(`${basePath}/folders`, { params }),
  createFolder: (payload) => axiosClient.post(`${basePath}/folders`, payload),
  updateFolder: (id, payload) => axiosClient.put(`${basePath}/folders/${id}`, payload),
  deleteFolder: (id) => axiosClient.delete(`${basePath}/folders/${id}`),

  getDocuments: (params) => axiosClient.get(basePath, { params }),
  getDocument: (id) => axiosClient.get(`${basePath}/${id}`),
  createDocument: (payload) => axiosClient.post(basePath, payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  updateDocument: (id, payload) => axiosClient.put(`${basePath}/${id}`, payload),
  deleteDocument: (id) => axiosClient.delete(`${basePath}/${id}`),
  restoreDocument: (id) => axiosClient.post(`${basePath}/${id}/restore`),
  downloadDocument: (id) => axiosClient.get(`${basePath}/${id}/download`, { responseType: 'blob' }),
  uploadDocumentVersion: (id, payload) => axiosClient.post(`${basePath}/${id}/versions`, payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  getDocumentVersions: (id) => axiosClient.get(`${basePath}/${id}/versions`),
  getDocumentAuditLogs: (id) => axiosClient.get(`${basePath}/${id}/audit-logs`),

  getDocumentPermissions: (id) => axiosClient.get(`${basePath}/${id}/permissions`),
  createDocumentPermission: (id, payload) => axiosClient.post(`${basePath}/${id}/permissions`, payload),
  updateDocumentPermission: (id, payload) => axiosClient.put(`${basePath}/permissions/${id}`, payload),
  deleteDocumentPermission: (id) => axiosClient.delete(`${basePath}/permissions/${id}`),

  verifyDocument: (id, payload) => axiosClient.post(`${basePath}/${id}/verify`, payload),
  rejectDocument: (id, payload) => axiosClient.post(`${basePath}/${id}/reject`, payload),
  getPendingVerifications: (params) => axiosClient.get(`${basePath}/verification/pending`, { params }),

  getTags: (params) => axiosClient.get(`${basePath}/tags`, { params }),
  createTag: (payload) => axiosClient.post(`${basePath}/tags`, payload),
  deleteTag: (id) => axiosClient.delete(`${basePath}/tags/${id}`),

  createBulkUpload: (payload) => axiosClient.post(`${basePath}/bulk-upload`, payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  getBulkUpload: (id) => axiosClient.get(`${basePath}/bulk-upload/${id}`),

  getExpiringDocuments: (params) => axiosClient.get(`${basePath}/reports/expiring`, { params }),
  getExpiredDocuments: (params) => axiosClient.get(`${basePath}/reports/expired`, { params }),
  getVerificationStatusReport: (params) => axiosClient.get(`${basePath}/reports/verification-status`, { params }),
  getStorageUsageReport: (params) => axiosClient.get(`${basePath}/reports/storage-usage`, { params }),
};
