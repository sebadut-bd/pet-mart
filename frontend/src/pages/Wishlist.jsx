import React, { useEffect, useState } from 'react';
import api from '../services/api';
import ProductCard from '../components/ProductCard';
import { Heart } from 'lucide-react';

const Wishlist = () => {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchWishlist();
  }, []);

  const fetchWishlist = async () => {
    try {
      const res = await api.get('/wishlist');
      if (res.data.status) {
        setItems(res.data.data);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: '1280px', margin: '2rem auto', padding: '0 2rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '1.5rem' }}>
        <Heart size={28} color="#ef4444" fill="#ef4444" />
        <div>
          <h1 style={{ fontSize: '1.6rem', fontWeight: 800 }}>Saved Listings ({items.length})</h1>
          <p style={{ color: '#64748b' }}>Quick access to pets and items you are comparing</p>
        </div>
      </div>

      {loading ? (
        <div style={{ textAlign: 'center', padding: '4rem' }}>Loading wishlist...</div>
      ) : items.length === 0 ? (
        <div style={{ background: 'white', padding: '4rem', textAlign: 'center', borderRadius: '16px', border: '1px solid #e2e8f0' }}>
          <h3>Your Wishlist is Empty</h3>
          <p style={{ color: '#64748b', marginTop: '6px' }}>Browse categories and tap the heart icon to save listings.</p>
        </div>
      ) : (
        <div className="product-grid">
          {items.map(product => (
            <ProductCard key={product.id} product={product} />
          ))}
        </div>
      )}
    </div>
  );
};

export default Wishlist;
