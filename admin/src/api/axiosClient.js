import axios from 'axios';
import { clearStoredSession, getAccessToken, getRefreshToken, getTenantCode, persistSession } from '../utils/tokenStorage';

const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1';
let isRefreshing = false;
let pendingRequests = [];

export const axiosClient = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

axiosClient.interceptors.request.use((config) => {
  const token = getAccessToken();
  const tenantCode = getTenantCode();

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  if (tenantCode) {
    config.headers['X-Tenant-Code'] = tenantCode;
  }

  return config;
});

function flushPendingRequests(error, token = null) {
  pendingRequests.forEach((request) => {
    if (error) {
      request.reject(error);
      return;
    }

    request.resolve(token);
  });

  pendingRequests = [];
}

axiosClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest?._retry) {
      const refreshToken = getRefreshToken();

      if (!refreshToken) {
        clearStoredSession();
        return Promise.reject(error);
      }

      if (isRefreshing) {
        return new Promise((resolve, reject) => {
          pendingRequests.push({
            resolve: (token) => {
              originalRequest.headers.Authorization = `Bearer ${token}`;
              resolve(axiosClient(originalRequest));
            },
            reject,
          });
        });
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        const response = await axios.post(`${baseURL}/auth/refresh`, { refresh_token: refreshToken }, {
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
        });

        const nextAccessToken = response.data.data.access_token;
        const nextRefreshToken = response.data.data.refresh_token;

        persistSession({
          accessToken: nextAccessToken,
          refreshToken: nextRefreshToken,
          tenantCode: getTenantCode(),
        });

        axiosClient.defaults.headers.Authorization = `Bearer ${nextAccessToken}`;
        flushPendingRequests(null, nextAccessToken);
        originalRequest.headers.Authorization = `Bearer ${nextAccessToken}`;

        return axiosClient(originalRequest);
      } catch (refreshError) {
        flushPendingRequests(refreshError, null);
        clearStoredSession();
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    }

    return Promise.reject(error);
  }
);
