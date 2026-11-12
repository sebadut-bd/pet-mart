import { useSearchParams } from 'react-router-dom';
import { useCallback } from 'react';

export function useQueryParams() {
  const [searchParams, setSearchParams] = useSearchParams();

  const getParam = useCallback((key, defaultValue = '') => {
    return searchParams.get(key) || defaultValue;
  }, [searchParams]);

  const setParam = useCallback((key, value) => {
    const next = new URLSearchParams(searchParams);
    if (value === '' || value === null || value === undefined) {
      next.delete(key);
    } else {
      next.set(key, String(value));
    }
    setSearchParams(next);
  }, [searchParams, setSearchParams]);

  const setParams = useCallback((updates) => {
    const next = new URLSearchParams(searchParams);
    Object.entries(updates).forEach(([key, value]) => {
      if (value === '' || value === null || value === undefined) {
        next.delete(key);
      } else {
        next.set(key, String(value));
      }
    });
    setSearchParams(next);
  }, [searchParams, setSearchParams]);

  const clearParams = useCallback(() => {
    setSearchParams(new URLSearchParams());
  }, [setSearchParams]);

  return { searchParams, getParam, setParam, setParams, clearParams };
}
