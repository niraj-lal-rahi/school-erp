import { axiosClient } from '../../../api/axiosClient';

export const masterDataApi = {
  getGuardians() {
    return axiosClient.get('/guardians');
  },
  createGuardian(payload) {
    return axiosClient.post('/guardians', payload);
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
};
