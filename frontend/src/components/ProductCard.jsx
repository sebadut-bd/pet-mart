import React from 'react';
import { Link } from 'react-router-dom';
import { MapPin, Heart } from 'lucide-react';
import VerifiedBadge from './VerifiedBadge';

const ProductCard = ({ product, onWishlistToggle }) => {
  return (
    <div className="product-card">
      <div className="product-img-wrapper">
        <Link to={`/product/${product.slug || product.id}`}>
          <img
            src={product.primary_image || 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=600'}
            alt={product.title}
            className="product-img"
          />
        </Link>
        {product.is_featured && <span className="badge-featured">Featured</span>}
      </div>

      <div className="product-info">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: '8px' }}>
          <span style={{ fontSize: '0.75rem', color: '#64748b', textTransform: 'uppercase', fontWeight: 600 }}>
            {product.category?.name}
          </span>
          {product.seller?.is_verified && <VerifiedBadge text="Verified" />}
        </div>

        <Link to={`/product/${product.slug || product.id}`}>
          <h3 className="product-title">{product.title}</h3>
        </Link>

        <div className="product-price">
          {product.price ? `৳ ${Number(product.price).toLocaleString()}` : 'Negotiable'}
          {product.is_negotiable && product.price && (
            <span style={{ fontSize: '0.75rem', color: '#64748b', marginLeft: '6px', fontWeight: 400 }}>
              (Negotiable)
            </span>
          )}
        </div>

        <div className="product-meta">
          <span style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
            <MapPin size={14} />
            {product.location?.area || product.location?.district || 'Bangladesh'}
          </span>
          <span>{new Date(product.created_at).toLocaleDateString()}</span>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
