import React, { useState } from 'react';
import { initials } from '../../lib/format';

/** Member photo, or their initials when there is no photo (or it fails to load). */
export function UserAvatar({ user, className = 'h-10 w-10 text-sm' }) {
  const [failed, setFailed] = useState(null);
  const src = user?.image;

  if (src && failed !== src) {
    return (
      <img
        src={src}
        alt=""
        onError={() => setFailed(src)}
        className={`shrink-0 rounded-full border border-border object-cover ${className}`}
      />
    );
  }
  return (
    <span
      className={`grid shrink-0 place-items-center rounded-full border border-primary/20 bg-primary/10 font-bold text-primary ${className}`}
      aria-hidden="true"
    >
      {initials(user?.name)}
    </span>
  );
}

export default UserAvatar;
