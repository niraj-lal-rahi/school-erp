import axios from 'axios';

const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1';

export const axiosClient = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

axiosClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('access_token');
  const tenantCode = localStorage.getItem('tenant_code');

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  if (tenantCode) {
    config.headers['X-Tenant-Code'] = tenantCode;
  }

  return config;
});

axiosClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('access_token');
    }

    return Promise.reject(error);
  }
);
