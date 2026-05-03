import { axiosClient } from './axiosClient';

export function createCrudApi(basePath) {
  return {
    list: (params) => axiosClient.get(basePath, { params }),
    show: (id, params) => axiosClient.get(`${basePath}/${id}`, { params }),
    create: (payload) => axiosClient.post(basePath, payload),
    update: (id, payload) => axiosClient.put(`${basePath}/${id}`, payload),
    remove: (id) => axiosClient.delete(`${basePath}/${id}`),
  };
}
