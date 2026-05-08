const API_BASE = '/api/v1/platform';
const TOKEN_KEY = 'superadmin_access_token';

async function request(path, options = {}) {
  const token = window.localStorage.getItem(TOKEN_KEY);
  const response = await fetch(`${API_BASE}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...(options.headers || {}),
    },
    credentials: 'include',
    ...options,
  });

  const contentType = response.headers.get('content-type') || '';
  const payload = contentType.includes('application/json')
    ? await response.json()
    : await response.text();

  if (!response.ok) {
    const message = typeof payload === 'object' && payload?.message
      ? payload.message
      : 'Platform request failed.';

    if (response.status === 401 && !options.skipAuthRedirect && typeof window !== 'undefined') {
      window.localStorage.removeItem(TOKEN_KEY);

      if (window.location.pathname !== '/superadmin/login') {
        window.location.assign('/superadmin/login');
      }
    }

    throw new Error(message);
  }

  return payload;
}

export const platformApi = {
  get: (path, options = {}) => request(path, options),
  post: (path, body = {}, options = {}) => request(path, { method: 'POST', body: JSON.stringify(body), ...options }),
  put: (path, body = {}, options = {}) => request(path, { method: 'PUT', body: JSON.stringify(body), ...options }),
};
