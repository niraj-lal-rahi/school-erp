import { axiosClient } from '../../../api/axiosClient';

export const studentApi = {
  getStudents(params) {
    return axiosClient.get('/students', { params });
  },
  getStudent(studentId) {
    return axiosClient.get(`/students/${studentId}`);
  },
  createStudent(payload) {
    return axiosClient.post('/students', payload);
  },
  updateStudent(studentId, payload) {
    return axiosClient.put(`/students/${studentId}`, payload);
  },
  uploadStudentDocument(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/documents`, payload, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },
  deleteStudent(studentId) {
    return axiosClient.delete(`/students/${studentId}`);
  },
};
