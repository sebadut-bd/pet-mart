// Where to send the user after login / register: router state, then ?redirect=, then a default.
export function getRedirectTarget(location, fallback = '/dashboard') {
  const from = location?.state?.from;
  let target = fallback;
  if (typeof from === 'string') target = from;
  else if (from?.pathname) target = `${from.pathname}${from.search || ''}`;
  else {
    const q = new URLSearchParams(location?.search || '').get('redirect');
    if (q) target = q;
  }
  // Only allow same-site paths (prevents open redirects like ?redirect=https://evil.example)
  if (!target.startsWith('/') || target.startsWith('//')) return fallback;
  if (['/login', '/register', '/forgot-password'].some((p) => target.startsWith(p))) return fallback;
  return target;
}

// Laravel 422 responses: { message, errors: { field: [msg] } }
export function fieldErrors(err) {
  const errors = err?.response?.data?.errors;
  if (!errors || typeof errors !== 'object') return {};
  return Object.fromEntries(Object.entries(errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : String(v)]));
}
