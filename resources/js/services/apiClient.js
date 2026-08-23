const TOKEN_KEY = 'artyweather_token';

export const authToken = {
    get() {
        return localStorage.getItem(TOKEN_KEY);
    },

    set(token) {
        if (token) {
            localStorage.setItem(TOKEN_KEY, token);
        } else {
            localStorage.removeItem(TOKEN_KEY);
        }
    },

    clear() {
        localStorage.removeItem(TOKEN_KEY);
    },
};

async function request(path, options = {}) {
    const token = authToken.get();

    const response = await fetch(`/api${path}`, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
            ...(options.headers || {}),
        },
    });

    if (response.status === 204) {
        return null;
    }

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        const error = new Error(
            payload.message || `Request failed with status ${response.status}`
        );

        error.status = response.status;
        error.payload = payload;

        throw error;
    }

    return payload.data ?? payload;
}

export const api = {
    me() {
        return request('/auth/me');
    },

    login(credentials) {
        return request('/auth/login', {
            method: 'POST',
            body: JSON.stringify(credentials),
        });
    },

    logout() {
        return request('/auth/logout', {
            method: 'POST',
        }).finally(() => {
            authToken.clear();
        });
    },

    weather() {
        return request('/weather');
    },

    weatherPresentation() {
        return request('/weather/presentation');
    },

    refreshWeather() {
        return request('/weather/refresh', {
            method: 'POST',
        });
    },

    settings() {
        return request('/weather-settings');
    },

    updateSettings(settings) {
        return request('/weather-settings', {
            method: 'PATCH',
            body: JSON.stringify(settings),
        });
    },
};