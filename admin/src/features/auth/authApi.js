import { axiosClient } from '../../api/axiosClient';

export const authApi = {
  login(payload) {
    return axiosClient.post('/auth/login', payload);
  },
  me() {
    return axiosClient.get('/auth/me');
  },
  refresh(payload) {
    return axiosClient.post('/auth/refresh', payload);
  },
  logout() {
    return axiosClient.post('/auth/logout');
  },
};
