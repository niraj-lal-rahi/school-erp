import { axiosClient } from '../../api/axiosClient';

export const authApi = {
  login(payload) {
    return axiosClient.post('/auth/login', payload);
  },
  me() {
    return axiosClient.get('/auth/me');
  },
  logout() {
    return axiosClient.post('/auth/logout');
  },
};
