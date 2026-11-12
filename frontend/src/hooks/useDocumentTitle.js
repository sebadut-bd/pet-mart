import { useEffect } from 'react';

const DEFAULT_DESCRIPTION =
  'PetMart Bangladesh – buy, sell and adopt pets, and trade pet food and supplies. Every listing is checked by our moderators.';

function setMeta(attr, key, content) {
  if (typeof document === 'undefined') return;
  let el = document.head.querySelector(`meta[${attr}="${key}"]`);
  if (!el) {
    el = document.createElement('meta');
    el.setAttribute(attr, key);
    document.head.appendChild(el);
  }
  el.setAttribute('content', content);
}

/**
 * Sets document title, meta description and basic Open Graph tags.
 * Note: this is client-side only; search engines that do not run JS will not see it.
 */
export function useDocumentTitle(title, { description, image } = {}) {
  useEffect(() => {
    const full = title ? `${title} | PetMart` : 'PetMart Bangladesh';
    const desc = description || DEFAULT_DESCRIPTION;
    document.title = full;
    setMeta('name', 'description', desc);
    setMeta('property', 'og:title', full);
    setMeta('property', 'og:description', desc);
    setMeta('property', 'og:type', 'website');
    setMeta('property', 'og:url', window.location.href);
    if (image) setMeta('property', 'og:image', image);
  }, [title, description, image]);
}
