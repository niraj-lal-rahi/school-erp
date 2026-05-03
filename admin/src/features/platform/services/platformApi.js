import { axiosClient } from '../../../api/axiosClient';

const basePath = '/platform';

export const platformApi = {
  getTenants: (params) => axiosClient.get(`${basePath}/tenants`, { params }),
  getTenant: (id) => axiosClient.get(`${basePath}/tenants/${id}`),
  activateTenant: (id) => axiosClient.post(`${basePath}/tenants/${id}/activate`),
  suspendTenant: (id) => axiosClient.post(`${basePath}/tenants/${id}/suspend`),
  cancelTenant: (id) => axiosClient.post(`${basePath}/tenants/${id}/cancel`),
  onboardSchool: (payload) => axiosClient.post(`${basePath}/tenants/onboard-school`, payload),
  getTenantBilling: (id) => axiosClient.get(`${basePath}/billing/tenants/${id}`),
  getTenantFeatures: (id) => axiosClient.get(`${basePath}/security/tenants/${id}/features`),
  getTenantUsage: (id) => axiosClient.get(`${basePath}/security/tenants/${id}/usage`),
  syncTenantUsage: (id) => axiosClient.post(`${basePath}/security/tenants/${id}/sync-usage`),
  getTenantDomains: (id) => axiosClient.get(`${basePath}/security/tenants/${id}/domains`),
  createTenantDomain: (id, payload) => axiosClient.post(`${basePath}/security/tenants/${id}/domains`, payload),
  verifyTenantDomain: (id) => axiosClient.post(`${basePath}/security/domains/${id}/verify`),
};
