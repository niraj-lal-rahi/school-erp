import { platformApi } from './platformApi';

export const monitoringService = {
  health: () => platformApi.get('/health'),
  tenantHealth: (tenantId) => platformApi.get(`/tenants/${tenantId}/health`),
  failedJobs: () => platformApi.get('/failed-jobs'),
  systemErrors: () => platformApi.get('/system-errors'),
  auditLogs: (query = '') => platformApi.get(`/audit-logs${query ? `?${query}` : ''}`),
};
