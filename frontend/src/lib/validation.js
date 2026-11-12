import { toast } from 'sonner';
import { scrollToElement } from './scroll';

export const isBlank = (v) => String(v ?? '').trim() === '';

/**
 * Show validation errors in a toast with the real messages, then focus the first field.
 * errors: { fieldKey: message }, ids: optional { fieldKey: elementId }
 */
export function toastErrors(errors, ids = {}) {
  const entries = Object.entries(errors).filter(([, m]) => m);
  if (!entries.length) return;
  if (entries.length === 1) {
    toast.error(entries[0][1]);
  } else {
    toast.error(`${entries.length} fields need your attention`, {
      description: entries.map(([, m]) => `• ${m}`).join('\n'),
      classNames: { description: 'whitespace-pre-line' },
    });
  }
  const id = ids[entries[0][0]] || entries[0][0];
  setTimeout(() => {
    const el = document.getElementById(id);
    scrollToElement(el, { center: true });
    el?.focus?.({ preventScroll: true });
  }, 50);
}
