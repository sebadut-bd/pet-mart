import React, { createContext, useContext, useState, useEffect } from 'react';
import api from '../services/api';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(localStorage.getItem('petmart_token') || null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (token) {
      fetchMe();
    } else {
      setLoading(false);
    }
  }, [token]);

  const fetchMe = async () => {
    try {
      const res = await api.get('/auth/me');
      if (res.data.status) {
        setUser(res.data.data);
      }
    } catch (err) {
      console.error('Failed to fetch profile', err);
      logout();
    } finally {
      setLoading(false);
    }
  };

  const login = async (phone, password) => {
    const res = await api.post('/auth/login', { phone, password });
    if (res.data.status) {
      const tokenVal = res.data.data.token;
      localStorage.setItem('petmart_token', tokenVal);
      setToken(tokenVal);
      setUser(res.data.data.user);
      return res.data;
    }
  };

  const register = async (name, phone, password, referral_code = '') => {
    const res = await api.post('/auth/register', { name, phone, password, referral_code });
    if (res.data.status) {
      const tokenVal = res.data.data.token;
      localStorage.setItem('petmart_token', tokenVal);
      setToken(tokenVal);
      setUser(res.data.data.user);
      return res.data;
    }
  };

  const logout = async () => {
    try {
      if (token) {
        await api.post('/auth/logout');
      }
    } catch (err) {
      console.error(err);
    } finally {
      localStorage.removeItem('petmart_token');
      setToken(null);
      setUser(null);
    }
  };

  return (
    <AuthContext.Provider value={{ user, token, loading, login, register, logout, fetchMe }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
