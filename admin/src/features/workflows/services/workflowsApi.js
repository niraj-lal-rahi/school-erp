import { axiosClient } from '../../../api/axiosClient';

const basePath = '/workflows';

export const workflowsApi = {
  getDefinitions: (params) => axiosClient.get(`${basePath}/definitions`, { params }),
  getDefinition: (id) => axiosClient.get(`${basePath}/definitions/${id}`),
  createDefinition: (payload) => axiosClient.post(`${basePath}/definitions`, payload),
  updateDefinition: (id, payload) => axiosClient.put(`${basePath}/definitions/${id}`, payload),
  deleteDefinition: (id) => axiosClient.delete(`${basePath}/definitions/${id}`),
  activateDefinition: (id) => axiosClient.post(`${basePath}/definitions/${id}/activate`),
  deactivateDefinition: (id) => axiosClient.post(`${basePath}/definitions/${id}/deactivate`),
  createStep: (definitionId, payload) => axiosClient.post(`${basePath}/definitions/${definitionId}/steps`, payload),
  updateStep: (id, payload) => axiosClient.put(`${basePath}/steps/${id}`, payload),
  deleteStep: (id) => axiosClient.delete(`${basePath}/steps/${id}`),

  startWorkflow: (payload) => axiosClient.post(`${basePath}/start`, payload),
  getInstances: (params) => axiosClient.get(`${basePath}/instances`, { params }),
  getInstance: (id) => axiosClient.get(`${basePath}/instances/${id}`),
  cancelInstance: (id, payload) => axiosClient.post(`${basePath}/instances/${id}/cancel`, payload),

  getApprovals: (params) => axiosClient.get(`${basePath}/approvals`, { params }),
  approveRequest: (id, payload) => axiosClient.post(`${basePath}/approvals/${id}/approve`, payload),
  rejectRequest: (id, payload) => axiosClient.post(`${basePath}/approvals/${id}/reject`, payload),

  getAutomations: (params) => axiosClient.get(`${basePath}/automations`, { params }),
  createAutomation: (payload) => axiosClient.post(`${basePath}/automations`, payload),
  updateAutomation: (id, payload) => axiosClient.put(`${basePath}/automations/${id}`, payload),
  deleteAutomation: (id) => axiosClient.delete(`${basePath}/automations/${id}`),
  runAutomation: (id, payload) => axiosClient.post(`${basePath}/automations/${id}/run`, payload),
  activateAutomation: (id) => axiosClient.post(`${basePath}/automations/${id}/activate`),
  deactivateAutomation: (id) => axiosClient.post(`${basePath}/automations/${id}/deactivate`),
  getAutomationRuns: (params) => axiosClient.get(`${basePath}/automation-runs`, { params }),

  getReminders: (params) => axiosClient.get(`${basePath}/reminders`, { params }),
  createReminder: (payload) => axiosClient.post(`${basePath}/reminders`, payload),
  updateReminder: (id, payload) => axiosClient.put(`${basePath}/reminders/${id}`, payload),
  deleteReminder: (id) => axiosClient.delete(`${basePath}/reminders/${id}`),
  processDueReminders: () => axiosClient.post(`${basePath}/reminders/process-due`),

  getWorkflowSummary: (params) => axiosClient.get(`${basePath}/reports/workflow-summary`, { params }),
  getAutomationSummary: (params) => axiosClient.get(`${basePath}/reports/automation-summary`, { params }),
  getApprovalPendingSummary: (params) => axiosClient.get(`${basePath}/reports/approval-pending`, { params }),

  getUsers: (params) => axiosClient.get('/rbac/users/1/roles', { params }).catch(() => ({ data: { data: [] } })),
};
