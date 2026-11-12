import { useEffect, useState } from 'react';
import api from '../services/api';

// Public site settings from the admin panel (GET /settings). Fetched once per page load.
let cache = null;
let inflight = null;

function load() {
  if (cache) return Promise.resolve(cache);
  if (!inflight) {
    inflight = api
      .get('/settings')
      .then((res) => {
        cache = res.data?.data || {};
        return cache;
      })
      .catch(() => {
        cache = {}; // fall back to built-in defaults if the API is unavailable
        return cache;
      })
      .finally(() => {
        inflight = null;
      });
  }
  return inflight;
}

export function useSiteSettings() {
  const [settings, setSettings] = useState(cache || {});
  const [loading, setLoading] = useState(!cache);

  useEffect(() => {
    if (cache) return undefined;
    let active = true;
    load().then((data) => {
      if (!active) return;
      setSettings(data);
      setLoading(false);
    });
    return () => {
      active = false;
    };
  }, []);

  return { settings, loading };
}

/** Setting value, or the fallback when it is missing/empty. */
export function setting(settings, key, fallback = '') {
  const v = settings?.[key];
  return v === null || v === undefined || v === '' ? fallback : v;
}
