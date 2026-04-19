import { axiosClient } from '../../../api/axiosClient';

export const masterDataApi = {
  getGuardians() {
    return axiosClient.get('/guardians');
  },
  createGuardian(payload) {
    return axiosClient.post('/guardians', payload);
  },
  updateGuardian(guardianId, payload) {
    return axiosClient.put(`/guardians/${guardianId}`, payload);
  },
  deleteGuardian(guardianId) {
    return axiosClient.delete(`/guardians/${guardianId}`);
  },
  getAcademicYears() {
    return axiosClient.get('/academic-years');
  },
  createAcademicYear(payload) {
    return axiosClient.post('/academic-years', payload);
  },
  getClasses() {
    return axiosClient.get('/classes');
  },
  createClass(payload) {
    return axiosClient.post('/classes', payload);
  },
  getSections() {
    return axiosClient.get('/sections');
  },
  createSection(payload) {
    return axiosClient.post('/sections', payload);
  },
  updateSection(sectionId, payload) {
    return axiosClient.put(`/sections/${sectionId}`, payload);
  },
  deleteSection(sectionId) {
    return axiosClient.delete(`/sections/${sectionId}`);
  },
};
