import { axiosClient } from '../../../api/axiosClient';

const basePath = '/portal';

function withStudentContext(studentId, config = {}) {
  const headers = { ...(config.headers || {}) };

  if (studentId) {
    headers['X-Portal-Student-Id'] = studentId;
  }

  return {
    ...config,
    headers,
  };
}

export const portalApi = {
  getContext: () => axiosClient.get(`${basePath}/context`),
  switchContext: (payload) => axiosClient.post(`${basePath}/context/switch`, payload),
  getProfiles: () => axiosClient.get(`${basePath}/profiles`),
  getAccessibleStudents: () => axiosClient.get(`${basePath}/accessible-students`),
  getDashboard: (studentId) => axiosClient.get(`${basePath}/dashboard`, withStudentContext(studentId)),

  getOverview: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/overview`, withStudentContext(studentId)),
  getAttendance: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/attendance`, withStudentContext(studentId)),
  getFees: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/fees`, withStudentContext(studentId)),
  getResults: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/results`, withStudentContext(studentId)),
  getTimetable: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/timetable`, withStudentContext(studentId)),
  getAssignments: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/assignments`, withStudentContext(studentId)),
  getTransport: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/transport`, withStudentContext(studentId)),
  getDocuments: (studentId) => axiosClient.get(`${basePath}/students/${studentId}/documents`, withStudentContext(studentId)),

  getNotifications: (params = {}) => axiosClient.get(`${basePath}/notifications`, { params }),
  markNotificationRead: (id) => axiosClient.post(`${basePath}/notifications/${id}/read`),
  markAllNotificationsRead: () => axiosClient.post(`${basePath}/notifications/read-all`),

  linkStudentProfile: (payload) => axiosClient.post(`${basePath}/profiles/link-student`, payload),
  linkGuardianProfile: (payload) => axiosClient.post(`${basePath}/profiles/link-guardian`, payload),
  updateProfileAccess: (id, payload) => axiosClient.put(`${basePath}/profiles/access/${id}`, payload),
};
