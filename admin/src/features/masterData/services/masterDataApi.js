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
  getStudentCategories() {
    return axiosClient.get('/student-categories');
  },
  createStudentCategory(payload) {
    return axiosClient.post('/student-categories', payload);
  },
  updateStudentCategory(categoryId, payload) {
    return axiosClient.put(`/student-categories/${categoryId}`, payload);
  },
  deleteStudentCategory(categoryId) {
    return axiosClient.delete(`/student-categories/${categoryId}`);
  },
  getStudentHouses() {
    return axiosClient.get('/student-houses');
  },
  createStudentHouse(payload) {
    return axiosClient.post('/student-houses', payload);
  },
  updateStudentHouse(houseId, payload) {
    return axiosClient.put(`/student-houses/${houseId}`, payload);
  },
  deleteStudentHouse(houseId) {
    return axiosClient.delete(`/student-houses/${houseId}`);
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
