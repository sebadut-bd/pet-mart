import { useCallback, useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { toast } from 'sonner';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';

/**
 * Save / unsave a listing via POST /wishlist/toggle.
 * Logged-out visitors get a toast with a login action instead of an API call.
 */
export function useWishlistToggle(productId, initial = false, onChange) {
  const { user } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [saved, setSaved] = useState(Boolean(initial));
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    setSaved(Boolean(initial));
  }, [initial]);

  const toggle = useCallback(async () => {
    if (!user) {
      toast('Log in to save listings', {
        action: { label: 'Log in', onClick: () => navigate('/login', { state: { from: location } }) },
      });
      return;
    }
    if (busy) return;
    setBusy(true);
    try {
      const res = await api.post('/wishlist/toggle', { product_id: productId });
      const next = Boolean(res.data?.is_wishlisted);
      setSaved(next);
      toast.success(next ? 'Saved to your list' : 'Removed from saved');
      onChange?.(next);
    } catch {
      toast.error('Could not update saved listings. Please try again.');
    } finally {
      setBusy(false);
    }
  }, [user, busy, productId, navigate, location, onChange]);

  return { saved, busy, toggle };
}
