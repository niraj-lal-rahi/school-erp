import { platformApi } from './platformApi';

export const tenantService = {
  list: (query = '') => platformApi.get(`/tenants${query ? `?${query}` : ''}`),
  detail: (tenantId) => platformApi.get(`/tenants/${tenantId}`),
  create: (payload) => platformApi.post('/tenants', payload),
  update: (tenantId, payload) => platformApi.put(`/tenants/${tenantId}`, payload),
  activate: (tenantId) => platformApi.post(`/tenants/${tenantId}/activate`),
  suspend: (tenantId) => platformApi.post(`/tenants/${tenantId}/suspend`),
  cancel: (tenantId) => platformApi.post(`/tenants/${tenantId}/cancel`),
  provisionDatabase: (tenantId, payload = {}) => platformApi.post(`/tenants/${tenantId}/provision-database`, payload),
  testDatabase: (tenantId) => platformApi.post(`/tenants/${tenantId}/test-database`),
  requestBackup: (tenantId, payload = {}) => platformApi.post(`/tenants/${tenantId}/backup`, payload),
  listBackups: (tenantId) => platformApi.get(`/tenants/${tenantId}/backups`),
};
