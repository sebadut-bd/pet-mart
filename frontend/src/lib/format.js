// Format price as "৳ 12,500". Null/empty price means the seller did not set one.
export function formatPrice(price, { isFree = false } = {}) {
  if (isFree) return 'Free';
  if (price === null || price === undefined || price === '') return 'Negotiable';
  const n = Number(price);
  if (Number.isNaN(n)) return 'Negotiable';
  if (n === 0) return 'Free';
  return `৳ ${n.toLocaleString('en-IN')}`;
}

// Format date as "8 Nov 2026"
export function formatDate(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Format relative time like "2 days ago"
export function formatRelativeTime(dateStr) {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  if (Number.isNaN(date.getTime())) return '';
  const diffSec = Math.floor((Date.now() - date.getTime()) / 1000);
  if (diffSec < 60) return 'just now';
  const units = [
    ['year', 31536000],
    ['month', 2592000],
    ['week', 604800],
    ['day', 86400],
    ['hour', 3600],
    ['minute', 60],
  ];
  for (const [unit, secs] of units) {
    const value = Math.floor(diffSec / secs);
    if (value >= 1) return `${value} ${unit}${value !== 1 ? 's' : ''} ago`;
  }
  return 'just now';
}

// Mask a phone number for logged-out visitors: 01712345678 -> 017XX-XXXXXX
export function maskPhone(phone) {
  if (!phone) return '';
  const digits = String(phone).replace(/\D/g, '');
  const local = digits.startsWith('880') ? `0${digits.slice(3)}` : digits;
  if (local.length < 4) return 'XXXXX-XXXXXX';
  return `${local.slice(0, 3)}XX-XXXXXX`;
}

// Location helpers – list and detail resources use different key names
export function formatLocation(location) {
  if (!location) return 'Bangladesh';
  const area = location.area || location.area_name;
  const district = location.district || location.district_name;
  const division = location.division || location.division_name;
  const parts = [area, district || division].filter(Boolean);
  if (parts.length) return parts.join(', ');
  return location.full_address || 'Bangladesh';
}

export function initials(name = '') {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((p) => p[0].toUpperCase())
    .join('') || '?';
}

export function humaniseKey(key = '') {
  return key.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
