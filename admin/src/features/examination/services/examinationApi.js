import { axiosClient } from '../../../api/axiosClient';

const basePath = '/exams';

export const examinationApi = {
  getExamTypes: (params) => axiosClient.get(`${basePath}/types`, { params }),
  createExamType: (payload) => axiosClient.post(`${basePath}/types`, payload),
  updateExamType: (id, payload) => axiosClient.put(`${basePath}/types/${id}`, payload),
  deleteExamType: (id) => axiosClient.delete(`${basePath}/types/${id}`),

  getExams: (params) => axiosClient.get(basePath, { params }),
  getExam: (id) => axiosClient.get(`${basePath}/${id}`),
  createExam: (payload) => axiosClient.post(basePath, payload),
  updateExam: (id, payload) => axiosClient.put(`${basePath}/${id}`, payload),
  deleteExam: (id) => axiosClient.delete(`${basePath}/${id}`),
  enrollStudents: (id) => axiosClient.post(`${basePath}/${id}/enroll-students`),

  getExamSubjects: (params) => axiosClient.get(`${basePath}/subjects/list`, { params }),
  createExamSubject: (payload) => axiosClient.post(`${basePath}/subjects`, payload),
  deleteExamSubject: (id) => axiosClient.delete(`${basePath}/subjects/${id}`),

  getExamMarks: (params) => axiosClient.get(`${basePath}/marks`, { params }),
  createExamMark: (payload) => axiosClient.post(`${basePath}/marks`, payload),
  bulkStoreExamMarks: (payload) => axiosClient.post(`${basePath}/marks/bulk`, payload),
  deleteExamMark: (id) => axiosClient.delete(`${basePath}/marks/${id}`),

  getGradingSystems: (params) => axiosClient.get(`${basePath}/grading-systems`, { params }),
  createGradingSystem: (payload) => axiosClient.post(`${basePath}/grading-systems`, payload),
  updateGradingSystem: (id, payload) => axiosClient.put(`${basePath}/grading-systems/${id}`, payload),
  deleteGradingSystem: (id) => axiosClient.delete(`${basePath}/grading-systems/${id}`),
  addGradeScale: (gradingSystemId, payload) => axiosClient.post(`${basePath}/grading-systems/${gradingSystemId}/scales`, payload),
  deleteGradeScale: (id) => axiosClient.delete(`${basePath}/grading-scales/${id}`),

  getResults: (params) => axiosClient.get(`${basePath}/results`, { params }),
  computeResults: (examId, payload) => axiosClient.post(`${basePath}/results/${examId}/compute`, payload),
  publishResults: (examId, payload) => axiosClient.post(`${basePath}/results/${examId}/publish`, payload),
  getStudentResult: (examId, studentId) => axiosClient.get(`${basePath}/results/${examId}/student/${studentId}`),
  getClassResults: (examId, classId, params) => axiosClient.get(`${basePath}/results/${examId}/class/${classId}`, { params }),
  getMeritList: (examId, params) => axiosClient.get(`${basePath}/results/${examId}/merit-list`, { params }),

  getRevaluations: (params) => axiosClient.get(`${basePath}/revaluation`, { params }),
  createRevaluation: (payload) => axiosClient.post(`${basePath}/revaluation`, payload),
  deleteRevaluation: (id) => axiosClient.delete(`${basePath}/revaluation/${id}`),

  getAcademicYears: (params) => axiosClient.get('/academic-years', { params }),
  getClasses: (params) => axiosClient.get('/classes', { params }),
  getSections: (params) => axiosClient.get('/sections', { params }),
  getStudents: (params) => axiosClient.get('/students', { params }),
  getTerms: (params) => axiosClient.get('/academic-management/terms', { params }),
  getSubjects: (params) => axiosClient.get('/academic-management/subjects', { params }),
};
