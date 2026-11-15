import axios from 'axios';
import { toast } from 'sonner';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1';
export const TOKEN_KEY = 'petmart_token';

export function getToken() {
  try {
    return localStorage.getItem(TOKEN_KEY);
  } catch {
    return null;
  }
}

export function setToken(token) {
  try {
    if (token) localStorage.setItem(TOKEN_KEY, token);
    else localStorage.removeItem(TOKEN_KEY);
  } catch {
    /* storage unavailable */
  }
}

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 20000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = getToken();
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Calls where a 401 is expected and handled by the caller (no redirect, no toast)
const SILENT_401 = ['/auth/login', '/auth/register', '/auth/me', '/auth/logout'];

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error.response?.status;
    const url = error.config?.url || '';
    if (status === 401 && !SILENT_401.some((p) => url.includes(p)) && getToken()) {
      setToken(null);
      window.dispatchEvent(new Event('petmart:logout'));
      const reason = error.response?.data?.message || '';
      toast.error(/deactivated/i.test(reason) ? reason : 'Your session has expired. Please log in again.');
      const here = window.location.pathname + window.location.search;
      if (!window.location.pathname.startsWith('/login')) {
        window.location.assign(`/login?redirect=${encodeURIComponent(here)}`);
      }
    }
    return Promise.reject(error);
  },
);

export default api;
