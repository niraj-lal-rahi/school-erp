import { axiosClient } from '../../../api/axiosClient';

export const enrollmentApi = {
  getEnrollments(params) {
    return axiosClient.get('/student-enrollments', { params });
  },
  createEnrollment(payload) {
    return axiosClient.post('/student-enrollments', payload);
  },
  updateEnrollment(enrollmentId, payload) {
    return axiosClient.put(`/student-enrollments/${enrollmentId}`, payload);
  },
};
