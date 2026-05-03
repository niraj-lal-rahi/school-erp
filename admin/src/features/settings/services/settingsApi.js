import { axiosClient } from '../../../api/axiosClient';

const basePath = '/settings';

export const settingsApi = {
  getGroups: (params) => axiosClient.get(`${basePath}/groups`, { params }),
  createGroup: (payload) => axiosClient.post(`${basePath}/groups`, payload),
  updateGroup: (id, payload) => axiosClient.put(`${basePath}/groups/${id}`, payload),
  deleteGroup: (id) => axiosClient.delete(`${basePath}/groups/${id}`),

  getSettings: (params) => axiosClient.get(basePath, { params }),
  createSetting: (payload) => axiosClient.post(basePath, payload),
  updateSetting: (id, payload) => axiosClient.put(`${basePath}/${id}`, payload),
  getSettingByKey: (key) => axiosClient.get(`${basePath}/by-key/${key}`),

  getFeatures: (params) => axiosClient.get(`${basePath}/features`, { params }),
  updateFeature: (id, payload) => axiosClient.put(`${basePath}/features/${id}`, payload),
  enableFeature: (id) => axiosClient.post(`${basePath}/features/${id}/enable`),
  disableFeature: (id) => axiosClient.post(`${basePath}/features/${id}/disable`),

  getBranding: () => axiosClient.get(`${basePath}/branding`),
  updateBranding: (payload) => axiosClient.put(`${basePath}/branding`, payload, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  }),

  getLocalization: () => axiosClient.get(`${basePath}/localization`),
  updateLocalization: (payload) => axiosClient.put(`${basePath}/localization`, payload),

  getSecurity: () => axiosClient.get(`${basePath}/security`),
  updateSecurity: (payload) => axiosClient.put(`${basePath}/security`, payload),

  getIntegrations: (params) => axiosClient.get(`${basePath}/integrations`, { params }),
  createIntegration: (payload) => axiosClient.post(`${basePath}/integrations`, payload),
  updateIntegration: (id, payload) => axiosClient.put(`${basePath}/integrations/${id}`, payload),
  deleteIntegration: (id) => axiosClient.delete(`${basePath}/integrations/${id}`),

  getAuditLogs: (params) => axiosClient.get(`${basePath}/audit-logs`, { params }),
  getPublicConfig: () => axiosClient.get(`${basePath}/public-config`),
};
