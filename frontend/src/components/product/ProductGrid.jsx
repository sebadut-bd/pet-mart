import React from 'react';
import { ProductCard } from './ProductCard';
import { SkeletonCard } from '../ui/Skeleton';

export function ProductGrid({
  products = [],
  loading = false,
  skeletonCount = 8,
  layout = 'grid',
  savedIds,
  onSavedChange,
  columns = 'grid-cols-1 min-[440px]:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
  imageAspect,
}) {
  const cls = layout === 'list' ? 'grid grid-cols-1 gap-4' : `grid ${columns} gap-4 sm:gap-5`;

  if (loading) {
    return (
      <div className={cls} aria-busy="true" aria-label="Loading listings">
        {Array.from({ length: skeletonCount }).map((_, i) => (
          <SkeletonCard key={i} />
        ))}
      </div>
    );
  }

  return (
    <div className={cls}>
      {products.map((p) => (
        <ProductCard
          key={p.id}
          product={p}
          layout={layout}
          imageAspect={imageAspect}
          saved={savedIds ? savedIds.has(p.id) : false}
          onSavedChange={onSavedChange ? (v) => onSavedChange(p, v) : undefined}
        />
      ))}
    </div>
  );
}

export default ProductGrid;
