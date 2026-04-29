import { axiosClient } from '../../../api/axiosClient';

const basePath = '/reports';

export const reportsApi = {
  getDashboard: (params) => axiosClient.get(`${basePath}/dashboard`, { params }),
  getWidgets: (params) => axiosClient.get(`${basePath}/widgets`, { params }),
  updateLayout: (payload) => axiosClient.put(`${basePath}/dashboard/layout`, payload),

  getDefinitions: (params) => axiosClient.get(`${basePath}/definitions`, { params }),
  getDefinition: (id) => axiosClient.get(`${basePath}/definitions/${id}`),
  createDefinition: (payload) => axiosClient.post(`${basePath}/definitions`, payload),
  updateDefinition: (id, payload) => axiosClient.put(`${basePath}/definitions/${id}`, payload),
  deleteDefinition: (id) => axiosClient.delete(`${basePath}/definitions/${id}`),

  runReport: (payload) => axiosClient.post(`${basePath}/run`, payload),
  getRuns: (params) => axiosClient.get(`${basePath}/runs`, { params }),
  getRun: (id) => axiosClient.get(`${basePath}/runs/${id}`),

  getSchedules: (params) => axiosClient.get(`${basePath}/schedules`, { params }),
  getSchedule: (id) => axiosClient.get(`${basePath}/schedules/${id}`),
  createSchedule: (payload) => axiosClient.post(`${basePath}/schedules`, payload),
  updateSchedule: (id, payload) => axiosClient.put(`${basePath}/schedules/${id}`, payload),
  pauseSchedule: (id) => axiosClient.post(`${basePath}/schedules/${id}/pause`),
  resumeSchedule: (id, payload = {}) => axiosClient.post(`${basePath}/schedules/${id}/resume`, payload),

  downloadExport: (id) => axiosClient.get(`${basePath}/exports/${id}/download`, {
    responseType: 'blob',
  }),

  getAcademicYears: () => axiosClient.get('/academic-years'),
  getClasses: () => axiosClient.get('/classes'),
  getSections: () => axiosClient.get('/sections'),
};
