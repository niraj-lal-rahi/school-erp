import { platformApi } from './platformApi';

export const planService = {
  list: () => platformApi.get('/plans'),
  detail: (planId) => platformApi.get(`/plans/${planId}`),
  create: (payload) => platformApi.post('/plans', payload),
  update: (planId, payload) => platformApi.put(`/plans/${planId}`, payload),
  features: (planId) => platformApi.get(`/plans/${planId}/features`),
  updateFeatures: (planId, payload) => platformApi.put(`/plans/${planId}/features`, payload),
  subscribeTenant: (tenantId, payload) => platformApi.post(`/tenants/${tenantId}/subscribe`, payload),
  changeTenantPlan: (tenantId, payload) => platformApi.post(`/tenants/${tenantId}/change-plan`, payload),
};
