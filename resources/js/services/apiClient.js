const TOKEN_KEY = 'artyweather_token';

export const authToken = {
  get: () => localStorage.getItem(TOKEN_KEY),
  set: (token) => token ? localStorage.setItem(TOKEN_KEY, token) : localStorage.removeItem(TOKEN_KEY),
};

async function request(path, options = {}) {
  const response = await fetch(`/api${path}`, {
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(authToken.get() ? { Authorization: `Bearer ${authToken.get()}` } : {}),
      ...options.headers,
    },
    ...options,
  });

  if (response.status === 204) return null;
  const payload = await response.json().catch(() => ({}));
  if (!response.ok) {
    const error = new Error(payload.message || 'Network request failed');
    error.status = response.status;
    error.payload = payload;
    throw error;
  }
  return payload.data ?? payload;
}

export const api = {
  me: () => request('/auth/me'),
  login: (credentials) => request('/auth/login', { method: 'POST', body: JSON.stringify(credentials) }),
  logout: () => request('/auth/logout', { method: 'POST' }).finally(() => authToken.set(null)),
  weather: () => request('/weather'),
  refreshWeather: () => request('/weather/refresh', { method: 'POST' }),
  settings: () => request('/weather-settings'),
  updateSettings: (settings) => request('/weather-settings', { method: 'PATCH', body: JSON.stringify(settings) }),
};
