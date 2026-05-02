import { axiosClient } from '../../../api/axiosClient';

const basePath = '/saas';

export const saasApi = {
  getTenants: (params) => axiosClient.get(`${basePath}/tenants`, { params }),
  getTenant: (id) => axiosClient.get(`${basePath}/tenants/${id}`),
  createTenant: (payload) => axiosClient.post(`${basePath}/tenants`, payload),
  updateTenant: (id, payload) => axiosClient.put(`${basePath}/tenants/${id}`, payload),
  activateTenant: (id) => axiosClient.post(`${basePath}/tenants/${id}/activate`),
  suspendTenant: (id) => axiosClient.post(`${basePath}/tenants/${id}/suspend`),
  cancelTenant: (id) => axiosClient.post(`${basePath}/tenants/${id}/cancel`),

  onboardSchool: (payload) => axiosClient.post(`${basePath}/onboard-school`, payload),

  getPlans: (params) => axiosClient.get(`${basePath}/plans`, { params }),
  createPlan: (payload) => axiosClient.post(`${basePath}/plans`, payload),
  updatePlan: (id, payload) => axiosClient.put(`${basePath}/plans/${id}`, payload),
  deletePlan: (id) => axiosClient.delete(`${basePath}/plans/${id}`),
  syncPlanFeatures: (id, payload) => axiosClient.post(`${basePath}/plans/${id}/features`, payload),

  subscribeTenant: (id, payload) => axiosClient.post(`${basePath}/tenants/${id}/subscribe`, payload),
  changeTenantPlan: (id, payload) => axiosClient.post(`${basePath}/tenants/${id}/change-plan`, payload),
  renewTenantPlan: (id, payload = {}) => axiosClient.post(`${basePath}/tenants/${id}/renew`, payload),
  cancelTenantSubscription: (id, payload = {}) => axiosClient.post(`${basePath}/tenants/${id}/cancel-subscription`, payload),

  getTenantFeatures: (id) => axiosClient.get(`${basePath}/tenants/${id}/features`),
  updateTenantFeatures: (id, payload) => axiosClient.put(`${basePath}/tenants/${id}/features`, payload),

  getTenantUsage: (id) => axiosClient.get(`${basePath}/tenants/${id}/usage`),
  syncTenantUsage: (id) => axiosClient.post(`${basePath}/tenants/${id}/sync-usage`),

  getTenantBilling: (id) => axiosClient.get(`${basePath}/tenants/${id}/billing`),
  markBillingPaid: (id) => axiosClient.post(`${basePath}/billing/${id}/mark-paid`),
  markBillingFailed: (id) => axiosClient.post(`${basePath}/billing/${id}/mark-failed`),

  getTenantDomains: (id) => axiosClient.get(`${basePath}/tenants/${id}/domains`),
  createTenantDomain: (id, payload) => axiosClient.post(`${basePath}/tenants/${id}/domains`, payload),
  verifyTenantDomain: (id) => axiosClient.post(`${basePath}/domains/${id}/verify`),
};
