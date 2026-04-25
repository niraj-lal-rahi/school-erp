import { axiosClient } from '../../../api/axiosClient';

const basePath = '/timetable';

export const timetableApi = {
  getOptions: () => axiosClient.get(`${basePath}/options`),

  getPeriods: (params) => axiosClient.get(`${basePath}/periods`, { params }),
  createPeriod: (payload) => axiosClient.post(`${basePath}/periods`, payload),
  updatePeriod: (id, payload) => axiosClient.put(`${basePath}/periods/${id}`, payload),
  deletePeriod: (id) => axiosClient.delete(`${basePath}/periods/${id}`),

  getRooms: (params) => axiosClient.get(`${basePath}/rooms`, { params }),
  createRoom: (payload) => axiosClient.post(`${basePath}/rooms`, payload),
  updateRoom: (id, payload) => axiosClient.put(`${basePath}/rooms/${id}`, payload),
  deleteRoom: (id) => axiosClient.delete(`${basePath}/rooms/${id}`),

  getVersions: (params) => axiosClient.get(`${basePath}/versions`, { params }),
  createVersion: (payload) => axiosClient.post(`${basePath}/versions`, payload),
  updateVersion: (id, payload) => axiosClient.put(`${basePath}/versions/${id}`, payload),
  deleteVersion: (id) => axiosClient.delete(`${basePath}/versions/${id}`),
  publishVersion: (id, payload = {}) => axiosClient.post(`${basePath}/versions/${id}/publish`, payload),
  archiveVersion: (id, payload = {}) => axiosClient.post(`${basePath}/versions/${id}/archive`, payload),
  duplicateVersion: (id) => axiosClient.post(`${basePath}/versions/${id}/duplicate`),

  getEntries: (params) => axiosClient.get(`${basePath}/entries`, { params }),
  createEntry: (payload) => axiosClient.post(`${basePath}/entries`, payload),
  updateEntry: (id, payload) => axiosClient.put(`${basePath}/entries/${id}`, payload),
  deleteEntry: (id) => axiosClient.delete(`${basePath}/entries/${id}`),
  bulkCreateEntries: (payload) => axiosClient.post(`${basePath}/entries/bulk-create`, payload),
  checkConflicts: (payload) => axiosClient.post(`${basePath}/entries/check-conflicts`, payload),

  getSubstitutions: (params) => axiosClient.get(`${basePath}/substitutions`, { params }),
  createSubstitution: (payload) => axiosClient.post(`${basePath}/substitutions`, payload),
  updateSubstitution: (id, payload) => axiosClient.put(`${basePath}/substitutions/${id}`, payload),
  deleteSubstitution: (id) => axiosClient.delete(`${basePath}/substitutions/${id}`),
  approveSubstitution: (id) => axiosClient.post(`${basePath}/substitutions/${id}/approve`),
  cancelSubstitution: (id) => axiosClient.post(`${basePath}/substitutions/${id}/cancel`),

  getExceptions: (params) => axiosClient.get(`${basePath}/exceptions`, { params }),
  createException: (payload) => axiosClient.post(`${basePath}/exceptions`, payload),
  updateException: (id, payload) => axiosClient.put(`${basePath}/exceptions/${id}`, payload),
  deleteException: (id) => axiosClient.delete(`${basePath}/exceptions/${id}`),

  getClassWeekly: (classId, sectionId, params) => axiosClient.get(`${basePath}/classes/${classId}/sections/${sectionId}/weekly`, { params }),
  getStaffWeekly: (staffId, params) => axiosClient.get(`${basePath}/staff/${staffId}/weekly`, { params }),
  getStaffDaily: (staffId, params) => axiosClient.get(`${basePath}/staff/${staffId}/daily`, { params }),

  getPublishLogs: (params) => axiosClient.get(`${basePath}/publish-logs`, { params }),
};
