import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import { scrollToTop } from '../../lib/scroll';

/** Jump to the top when the route changes (instant, so it doesn't animate across pages). */
export function ScrollToTop() {
  const { pathname, search } = useLocation();

  useEffect(() => {
    scrollToTop({ immediate: true });
  }, [pathname, search]);

  return null;
}

export default ScrollToTop;
