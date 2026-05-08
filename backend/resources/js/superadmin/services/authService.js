import { platformApi } from './platformApi';

const TOKEN_KEY = 'superadmin_access_token';

function getToken() {
  return window.localStorage.getItem(TOKEN_KEY);
}

function setToken(token) {
  window.localStorage.setItem(TOKEN_KEY, token);
}

function clearToken() {
  window.localStorage.removeItem(TOKEN_KEY);
}

export const superAdminAuth = {
  tokenKey: TOKEN_KEY,
  getToken,
  setToken,
  clearToken,
  isAuthenticated() {
    return Boolean(getToken());
  },
  async login(credentials) {
    const payload = await platformApi.post('/auth/login', credentials, { skipAuthRedirect: true });
    const token = payload?.data?.access_token;

    if (!token) {
      throw new Error('Platform login did not return an access token.');
    }

    setToken(token);

    return payload;
  },
  async me() {
    return platformApi.get('/auth/me', { skipAuthRedirect: true });
  },
  async logout() {
    try {
      await platformApi.post('/auth/logout', {}, { skipAuthRedirect: true });
    } finally {
      clearToken();
    }
  },
};
