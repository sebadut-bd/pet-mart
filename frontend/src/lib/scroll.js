// Scroll helpers that work with or without Lenis smooth scrolling.
// The active Lenis instance is registered by <SmoothScroll>.

let lenis = null;
let lockCount = 0;

const HEADER_OFFSET = -88; // fixed navbar (72px) + breathing room

export function setLenis(instance) {
  lenis = instance || null;
}

export function getLenis() {
  return lenis;
}

export function scrollToTop({ immediate = false } = {}) {
  if (lenis) lenis.scrollTo(0, { immediate, force: true });
  else window.scrollTo({ top: 0, left: 0, behavior: immediate ? 'instant' : 'smooth' });
}

export function scrollToElement(el, { offset = HEADER_OFFSET, center = false } = {}) {
  if (!el) return;
  if (lenis) {
    const centreOffset = center ? -(window.innerHeight / 2 - el.getBoundingClientRect().height / 2) : offset;
    lenis.scrollTo(el, { offset: centreOffset });
  } else {
    el.scrollIntoView({ behavior: 'smooth', block: center ? 'center' : 'start' });
  }
}

/** Freeze page scrolling while a modal/drawer is open. Calls can be nested. */
export function lockScroll() {
  lockCount += 1;
  if (lockCount === 1) {
    lenis?.stop();
    document.body.style.overflow = 'hidden';
  }
}

export function unlockScroll() {
  lockCount = Math.max(0, lockCount - 1);
  if (lockCount === 0) {
    document.body.style.overflow = '';
    lenis?.start();
  }
}
