import { platformApi } from './platformApi';

export const settingsService = {
  listGroups: () => platformApi.get('/settings'),
  getGroup: (group) => platformApi.get(`/settings/${group}`),
  updateGroup: (group, payload) => platformApi.put(`/settings/${group}`, payload),
  featureFlags: () => platformApi.get('/features'),
  updateFeatureFlag: (featureCode, payload) => platformApi.put(`/features/${featureCode}`, payload),
  tenantFeatureFlags: (tenantId) => platformApi.get(`/tenants/${tenantId}/features`),
  updateTenantFeatureFlags: (tenantId, payload) => platformApi.put(`/tenants/${tenantId}/features`, payload),
};
