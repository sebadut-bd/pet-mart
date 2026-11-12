import React, { useEffect, useState } from 'react';
import { ReactLenis, useLenis } from 'lenis/react';
import 'lenis/dist/lenis.css';
import { setLenis } from '../../lib/scroll';

const QUERY = '(prefers-reduced-motion: reduce)';

function usePrefersReducedMotion() {
  const [reduced, setReduced] = useState(() => typeof window !== 'undefined' && window.matchMedia?.(QUERY).matches);
  useEffect(() => {
    const mq = window.matchMedia?.(QUERY);
    if (!mq) return undefined;
    const onChange = () => setReduced(mq.matches);
    mq.addEventListener('change', onChange);
    return () => mq.removeEventListener('change', onChange);
  }, []);
  return reduced;
}

// Makes the Lenis instance available to plain helpers in lib/scroll.js
function LenisBridge() {
  const lenis = useLenis();
  useEffect(() => {
    setLenis(lenis);
    return () => setLenis(null);
  }, [lenis]);
  return null;
}

/**
 * Smooth (inertia) scrolling for the whole page using Lenis.
 * Turned off for people who ask their system for reduced motion.
 * Elements with `data-lenis-prevent` (modals, drawers, menus) keep native scrolling.
 */
export function SmoothScroll({ children }) {
  const reduced = usePrefersReducedMotion();
  if (reduced) return children;

  return (
    <ReactLenis
      root
      options={{
        lerp: 0.09, // lower = smoother/slower
        wheelMultiplier: 1,
        smoothWheel: true,
        anchors: true, // #anchors use each section's scroll-margin-top to clear the fixed navbar
        autoRaf: true,
      }}
    >
      <LenisBridge />
      {children}
    </ReactLenis>
  );
}

export default SmoothScroll;
