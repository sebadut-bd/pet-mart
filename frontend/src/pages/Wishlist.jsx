import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { Heart } from 'lucide-react';
import api from '../services/api';
import { useDocumentTitle } from '../hooks/useDocumentTitle';
import { AccountLayout } from '../components/layout/AccountLayout';
import { EmptyState } from '../components/ui/EmptyState';
import { ProductGrid } from '../components/product/ProductGrid';

export default function Wishlist() {
  useDocumentTitle('Saved listings');
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(false);

  const load = useCallback(() => {
    setLoading(true);
    setError(false);
    api
      .get('/wishlist')
      .then((res) => setItems(res.data?.data || []))
      .catch(() => setError(true))
      .finally(() => setLoading(false));
  }, []);

  useEffect(load, [load]);

  const savedIds = useMemo(() => new Set(items.map((i) => i.id)), [items]);

  // Unsaving from the card removes it from this list
  const onSavedChange = (product, isSaved) => {
    if (!isSaved) setItems((list) => list.filter((p) => p.id !== product.id));
  };

  return (
    <AccountLayout title="Saved listings" subtitle="Pets and items you've saved to compare later.">
      {error ? (
        <div className="rounded-2xl border border-border bg-white">
          <EmptyState icon={Heart} title="Saved listings could not be loaded" action={load} actionLabel="Try again" />
        </div>
      ) : !loading && items.length === 0 ? (
        <div className="rounded-2xl border border-border bg-white">
          <EmptyState
            icon={Heart}
            title="Nothing saved yet"
            description="Tap the heart on any listing to save it here."
            actionTo="/browse"
            actionLabel="Browse listings"
          />
        </div>
      ) : (
        <ProductGrid
          products={items}
          loading={loading}
          skeletonCount={6}
          savedIds={savedIds}
          onSavedChange={onSavedChange}
          columns="grid-cols-1 min-[440px]:grid-cols-2 xl:grid-cols-3"
        />
      )}
    </AccountLayout>
  );
}
