import { axiosClient } from '../../../api/axiosClient';

const basePath = '/attendance';

export const attendanceApi = {
  getStatusTypes: (params) => axiosClient.get(`${basePath}/status-types`, { params }),
  createStatusType: (payload) => axiosClient.post(`${basePath}/status-types`, payload),
  updateStatusType: (id, payload) => axiosClient.put(`${basePath}/status-types/${id}`, payload),
  deleteStatusType: (id) => axiosClient.delete(`${basePath}/status-types/${id}`),

  getPeriods: (params) => axiosClient.get(`${basePath}/periods`, { params }),
  createPeriod: (payload) => axiosClient.post(`${basePath}/periods`, payload),
  updatePeriod: (id, payload) => axiosClient.put(`${basePath}/periods/${id}`, payload),
  deletePeriod: (id) => axiosClient.delete(`${basePath}/periods/${id}`),

  getStudentSessions: (params) => axiosClient.get(`${basePath}/student-sessions`, { params }),
  getStudentSession: (id) => axiosClient.get(`${basePath}/student-sessions/${id}`),
  createStudentSession: (payload) => axiosClient.post(`${basePath}/student-sessions`, payload),
  updateStudentSession: (id, payload) => axiosClient.put(`${basePath}/student-sessions/${id}`, payload),
  deleteStudentSession: (id) => axiosClient.delete(`${basePath}/student-sessions/${id}`),
  bulkMarkSession: (id, payload) => axiosClient.post(`${basePath}/student-sessions/${id}/bulk-mark`, payload),
  submitStudentSession: (id) => axiosClient.post(`${basePath}/student-sessions/${id}/submit`),
  lockStudentSession: (id) => axiosClient.post(`${basePath}/student-sessions/${id}/lock`),
  markStudentAttendance: (studentId, payload) => axiosClient.post(`${basePath}/students/${studentId}/mark`, payload),

  getStudentRecords: (params) => axiosClient.get(`${basePath}/student-records`, { params }),
  updateStudentRecord: (id, payload) => axiosClient.put(`${basePath}/student-records/${id}`, payload),
  deleteStudentRecord: (id) => axiosClient.delete(`${basePath}/student-records/${id}`),

  getStaffRecords: (params) => axiosClient.get(`${basePath}/staff-records`, { params }),
  createStaffRecord: (payload) => axiosClient.post(`${basePath}/staff-records`, payload),
  updateStaffRecord: (id, payload) => axiosClient.put(`${basePath}/staff-records/${id}`, payload),
  deleteStaffRecord: (id) => axiosClient.delete(`${basePath}/staff-records/${id}`),
  markStaffAttendance: (staffId, payload) => axiosClient.post(`${basePath}/staff/${staffId}/mark`, payload),

  getCorrections: (params) => axiosClient.get(`${basePath}/corrections`, { params }),
  createCorrection: (payload) => axiosClient.post(`${basePath}/corrections`, payload),
  approveCorrection: (id, payload) => axiosClient.post(`${basePath}/corrections/${id}/approve`, payload),
  rejectCorrection: (id, payload) => axiosClient.post(`${basePath}/corrections/${id}/reject`, payload),

  getImports: (params) => axiosClient.get(`${basePath}/imports`, { params }),
  createImport: (payload) => axiosClient.post(`${basePath}/imports`, payload),
  processImport: (id) => axiosClient.post(`${basePath}/imports/${id}/process`),

  getBiometricLogs: (params) => axiosClient.get(`${basePath}/biometric-logs`, { params }),
  createBiometricLog: (payload) => axiosClient.post(`${basePath}/biometric-logs`, payload),
  processBiometricLogs: (payload) => axiosClient.post(`${basePath}/biometric-logs/process`, payload),

  getHolidays: (params) => axiosClient.get(`${basePath}/holidays`, { params }),
  createHoliday: (payload) => axiosClient.post(`${basePath}/holidays`, payload),
  updateHoliday: (id, payload) => axiosClient.put(`${basePath}/holidays/${id}`, payload),
  deleteHoliday: (id) => axiosClient.delete(`${basePath}/holidays/${id}`),

  getSummary: (params) => axiosClient.get(`${basePath}/summary`, { params }),
  refreshSummary: (payload) => axiosClient.post(`${basePath}/summary/refresh`, payload),

  getStudentSummaryReport: (params) => axiosClient.get(`${basePath}/reports/student-summary`, { params }),
  getStaffSummaryReport: (params) => axiosClient.get(`${basePath}/reports/staff-summary`, { params }),
  getClassAttendanceReport: (params) => axiosClient.get(`${basePath}/reports/class-attendance`, { params }),
  getDefaultersReport: (params) => axiosClient.get(`${basePath}/reports/defaulters`, { params }),

  getAcademicYears: (params) => axiosClient.get('/academic-years', { params }),
  getClasses: (params) => axiosClient.get('/classes', { params }),
  getSections: (params) => axiosClient.get('/sections', { params }),
  getStudents: (params) => axiosClient.get('/students', { params }),
  getStaff: (params) => axiosClient.get('/hr/staff', { params }),
  getSubjects: (params) => axiosClient.get('/academic-management/subjects', { params }),
};
