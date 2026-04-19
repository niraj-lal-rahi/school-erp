import { axiosClient } from '../../api/axiosClient';

export const dashboardApi = {
  getOverview() {
    return axiosClient.get('/dashboard/overview');
  },
};
