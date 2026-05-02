import { axiosClient } from '../../../api/axiosClient';

const basePath = '/rbac';

export const rbacApi = {
  getPermissions: (params) => axiosClient.get(`${basePath}/permissions`, { params }),
  getGroupedPermissions: (params) => axiosClient.get(`${basePath}/permissions/grouped`, { params }),
  syncPermissionsCatalog: () => axiosClient.post(`${basePath}/permissions/sync`),

  getRoles: (params) => axiosClient.get(`${basePath}/roles`, { params }),
  createRole: (payload) => axiosClient.post(`${basePath}/roles`, payload),
  updateRole: (id, payload) => axiosClient.put(`${basePath}/roles/${id}`, payload),
  deleteRole: (id) => axiosClient.delete(`${basePath}/roles/${id}`),
  cloneRole: (id, payload) => axiosClient.post(`${basePath}/roles/${id}/clone`, payload),
  syncRolePermissions: (id, payload) => axiosClient.post(`${basePath}/roles/${id}/permissions`, payload),

  getUserRoles: (userId) => axiosClient.get(`${basePath}/users/${userId}/roles`),
  assignUserRole: (userId, payload) => axiosClient.post(`${basePath}/users/${userId}/roles`, payload),
  removeUserRole: (userId, roleId) => axiosClient.delete(`${basePath}/users/${userId}/roles/${roleId}`),

  getMyPermissions: () => axiosClient.get(`${basePath}/me/permissions`),
  getMyRoles: () => axiosClient.get(`${basePath}/me/roles`),
  checkPermission: (payload) => axiosClient.post(`${basePath}/check-permission`, payload),
};
