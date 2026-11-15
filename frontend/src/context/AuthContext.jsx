import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import api, { getToken, setToken as storeToken } from '../services/api';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(getToken);
  const [loading, setLoading] = useState(Boolean(getToken()));

  const clearSession = useCallback(() => {
    storeToken(null);
    setToken(null);
    setUser(null);
  }, []);

  const fetchMe = useCallback(async () => {
    try {
      const res = await api.get('/auth/me');
      if (res.data?.status && res.data.data) setUser(res.data.data);
      else clearSession();
    } catch {
      // Invalid or expired token: drop it quietly; public pages keep working
      clearSession();
    } finally {
      setLoading(false);
    }
  }, [clearSession]);

  useEffect(() => {
    if (token && !user) fetchMe();
    else setLoading(false);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [token]);

  // The API interceptor fires this when a request returns 401
  useEffect(() => {
    window.addEventListener('petmart:logout', clearSession);
    return () => window.removeEventListener('petmart:logout', clearSession);
  }, [clearSession]);

  const startSession = (res) => {
    if (!res.data?.status || !res.data?.data?.token) {
      throw new Error(res.data?.message || 'Unexpected response from server.');
    }
    storeToken(res.data.data.token);
    setUser(res.data.data.user);
    setToken(res.data.data.token);
    return res.data;
  };

  const login = async (phone, password) => startSession(await api.post('/auth/login', { phone, password }));

  const register = async (name, phone, password, referral_code = '') =>
    startSession(await api.post('/auth/register', { name, phone, password, referral_code: referral_code || undefined }));

  const logout = useCallback(async () => {
    try {
      if (getToken()) await api.post('/auth/logout');
    } catch {
      /* token may already be invalid */
    } finally {
      clearSession();
    }
  }, [clearSession]);

  const value = useMemo(
    () => ({ user, token, loading, login, register, logout, fetchMe, setUser }),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [user, token, loading, logout, fetchMe],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => useContext(AuthContext);
