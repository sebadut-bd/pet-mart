import React from 'react';
import { Link } from 'react-router-dom';
import { Construction, Phone, KeyRound, ShieldCheck, ArrowLeft } from 'lucide-react';
import { useDocumentTitle } from '../hooks/useDocumentTitle';
import { AuthLayout } from '../components/layout/AuthLayout';
import { Button } from '../components/ui/Button';

/**
 * Password reset is not available yet: the backend endpoint is broken (returns an invalid
 * HTTP status) and there is no verify-OTP / reset endpoint. This page explains that instead
 * of pretending a reset happened.
 *
 * Backend requirements:
 *   POST /api/v1/auth/forgot-password  { phone }            -> sends OTP by SMS (do not return the code)
 *   POST /api/v1/auth/verify-otp       { phone, otp }        -> returns a short-lived reset token
 *   POST /api/v1/auth/reset-password   { token, password }   -> sets the new password
 */
const STEPS = [
  { icon: Phone, label: 'Enter your mobile number' },
  { icon: ShieldCheck, label: 'Enter the code we send by SMS' },
  { icon: KeyRound, label: 'Choose a new password' },
];

export default function ForgotPassword() {
  useDocumentTitle('Reset password');
  return (
    <AuthLayout title="Reset your password" subtitle="Online password reset is coming soon.">
      <div className="flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
        <Construction size={20} className="shrink-0 text-amber-600" aria-hidden="true" />
        <p>
          We're still building self-service password reset. Until then, please contact our support team from the{' '}
          <Link to="/contact" className="font-semibold underline">
            Contact page
          </Link>{' '}
          and we'll help you get back into your account.
        </p>
      </div>

      <ol className="mt-6 space-y-3" aria-label="How reset will work">
        {STEPS.map(({ icon: Icon, label }, i) => (
          <li key={label} className="flex items-center gap-3 rounded-lg border border-dashed border-border px-4 py-3 text-sm text-muted">
            <span className="grid h-8 w-8 place-items-center rounded-full bg-slate-100 text-slate-500">
              <Icon size={16} aria-hidden="true" />
            </span>
            <span>
              <span className="font-semibold">Step {i + 1}:</span> {label}
            </span>
          </li>
        ))}
      </ol>

      <div className="mt-6 flex flex-col gap-3 sm:flex-row">
        <Button as="link" to="/contact" className="flex-1">
          Contact support
        </Button>
        <Button as="link" to="/login" variant="outline" className="flex-1" iconLeft={<ArrowLeft size={16} />}>
          Back to log in
        </Button>
      </div>
    </AuthLayout>
  );
}
