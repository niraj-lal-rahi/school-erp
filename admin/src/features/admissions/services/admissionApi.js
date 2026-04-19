import { axiosClient } from '../../../api/axiosClient';

export const admissionApi = {
  getAdmissions(params) {
    return axiosClient.get('/student-admissions', { params });
  },
  getAdmission(admissionId) {
    return axiosClient.get(`/student-admissions/${admissionId}`);
  },
  createAdmission(payload) {
    return axiosClient.post('/student-admissions', payload);
  },
  updateAdmission(admissionId, payload) {
    return axiosClient.put(`/student-admissions/${admissionId}`, payload);
  },
  submitAdmission(admissionId) {
    return axiosClient.post(`/student-admissions/${admissionId}/submit`);
  },
  reviewAdmission(admissionId, payload) {
    return axiosClient.post(`/student-admissions/${admissionId}/review`, payload);
  },
  approveAdmission(admissionId, payload) {
    return axiosClient.post(`/student-admissions/${admissionId}/approve`, payload);
  },
  rejectAdmission(admissionId, payload) {
    return axiosClient.post(`/student-admissions/${admissionId}/reject`, payload);
  },
  waitlistAdmission(admissionId, payload) {
    return axiosClient.post(`/student-admissions/${admissionId}/waitlist`, payload);
  },
  convertAdmission(admissionId, payload) {
    return axiosClient.post(`/student-admissions/${admissionId}/convert-to-student`, payload);
  },
};
