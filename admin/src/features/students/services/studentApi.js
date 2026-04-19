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
  saveStudentMedical(studentId, payload) {
    return axiosClient.put(`/students/${studentId}/medical`, payload);
  },
  addStudentNote(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/notes`, payload);
  },
  updateStudentNote(noteId, payload) {
    return axiosClient.put(`/student-notes/${noteId}`, payload);
  },
  deleteStudentNote(noteId) {
    return axiosClient.delete(`/student-notes/${noteId}`);
  },
  promoteStudent(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/promote`, payload);
  },
  transferStudentSection(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/transfer-section`, payload);
  },
  withdrawStudent(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/withdraw`, payload);
  },
  graduateStudent(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/graduate`, payload);
  },
  suspendStudent(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/suspend`, payload);
  },
  reactivateStudent(studentId, payload) {
    return axiosClient.post(`/students/${studentId}/reactivate`, payload);
  },
  deleteStudentDocument(documentId) {
    return axiosClient.delete(`/student-documents/${documentId}`);
  },
  deleteStudent(studentId) {
    return axiosClient.delete(`/students/${studentId}`);
  },
};
