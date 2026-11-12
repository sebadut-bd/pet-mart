import React from 'react';
import { BadgeCheck } from 'lucide-react';

export function VerifiedBadge({ text = 'Verified seller', className = '' }) {
  return (
    <span
      className={`inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200 ${className}`}
    >
      <BadgeCheck size={13} aria-hidden="true" />
      {text}
    </span>
  );
}

export default VerifiedBadge;
