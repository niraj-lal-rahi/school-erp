import { platformApi } from './platformApi';

export const securityService = {
  startImpersonation: (tenantId, payload) => platformApi.post(`/tenants/${tenantId}/impersonate`, payload),
  stopImpersonation: () => platformApi.post('/impersonation/stop'),
  requestEmergencyAccess: (tenantId, payload) => platformApi.post(`/tenants/${tenantId}/emergency-access/request`, payload),
  approveEmergencyAccess: (accessId, payload = {}) => platformApi.post(`/emergency-access/${accessId}/approve`, payload),
  revokeEmergencyAccess: (accessId, payload) => platformApi.post(`/emergency-access/${accessId}/revoke`, payload),
};
