export function getAccessToken() {
  return localStorage.getItem('access_token') || '';
}

export function getRefreshToken() {
  return localStorage.getItem('refresh_token') || '';
}

export function getTenantCode() {
  return localStorage.getItem('tenant_code') || '';
}

export function persistSession({ accessToken, refreshToken, tenantCode }) {
  if (accessToken) localStorage.setItem('access_token', accessToken);
  if (refreshToken) localStorage.setItem('refresh_token', refreshToken);
  if (tenantCode) {
    localStorage.setItem('tenant_code', tenantCode);
  } else {
    localStorage.removeItem('tenant_code');
  }
}

export function clearStoredSession() {
  localStorage.removeItem('access_token');
  localStorage.removeItem('refresh_token');
  localStorage.removeItem('tenant_code');
}
