import React from 'react';

export function Card({ children, className = '', padding = true, ...props }) {
  return (
    <div
      className={`bg-white border border-border rounded-2xl shadow-sm ${padding ? 'p-5' : ''} ${className}`}
      {...props}
    >
      {children}
    </div>
  );
}
