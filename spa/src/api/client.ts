import axios, { isAxiosError, type AxiosError } from 'axios';
import { clearAuth, getToken } from '../auth/storage';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = getToken();

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error: AxiosError) => {
        const status = error.response?.status;
        const requestUrl = error.config?.url ?? '';
        const isLoginRequest = requestUrl.includes('/auth/login');

        if (status === 401 && !isLoginRequest) {
            clearAuth();

            if (window.location.pathname !== '/login') {
                window.location.assign('/login');
            }
        }

        return Promise.reject(error);
    },
);

export function apiErrorMessage(error: unknown, fallback: string): string {
    if (!isAxiosError(error)) {
        return fallback;
    }

    const data = error.response?.data;

    if (data && typeof data === 'object' && 'message' in data && typeof data.message === 'string') {
        return data.message;
    }

    return fallback;
}

export default api;
