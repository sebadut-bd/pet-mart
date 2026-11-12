import React from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Spinner } from '../components/ui/Spinner';
import { getRedirectTarget } from '../lib/auth';

/**
 * Routes only for guests (login, register, forgot password).
 * Logged-in users are sent back to where they came from.
 * Waits for AuthContext loading before deciding.
 */
export function GuestRoute({ children }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="flex min-h-[60vh] items-center justify-center">
        <Spinner size={36} />
      </div>
    );
  }

  if (user) {
    return <Navigate to={getRedirectTarget(location, '/')} replace />;
  }

  return children;
}
