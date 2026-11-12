import { useEffect, useState } from 'react';
import api from '../services/api';

// Categories and locations rarely change, so cache them for the session.
let cache = null;
let inflight = null;

function load() {
  if (cache) return Promise.resolve(cache);
  if (!inflight) {
    inflight = Promise.all([api.get('/categories'), api.get('/locations')])
      .then(([catRes, locRes]) => {
        cache = {
          categories: catRes.data?.data || [],
          locations: locRes.data?.data || { divisions: [], districts: [], areas: [] },
        };
        return cache;
      })
      .finally(() => {
        inflight = null;
      });
  }
  return inflight;
}

// Flatten root categories and their children into one list
export function flattenCategories(categories = []) {
  const out = [];
  categories.forEach((c) => {
    out.push({ ...c, depth: 0 });
    (c.children || []).forEach((child) => out.push({ ...child, depth: 1, parentName: c.name }));
  });
  return out;
}

export function useCatalog() {
  const [state, setState] = useState({
    categories: cache?.categories || [],
    locations: cache?.locations || { divisions: [], districts: [], areas: [] },
    loading: !cache,
    error: null,
  });

  useEffect(() => {
    let active = true;
    if (cache) return undefined;
    load()
      .then((data) => active && setState({ ...data, loading: false, error: null }))
      .catch((error) => active && setState((s) => ({ ...s, loading: false, error })));
    return () => {
      active = false;
    };
  }, []);

  return state;
}
