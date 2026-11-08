import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import VerifiedBadge from '../components/VerifiedBadge';
import { PlusCircle, CheckCircle, Trash2, Tag, Eye } from 'lucide-react';

const SellerDashboard = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [listings, setListings] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) {
      navigate('/login');
      return;
    }
    fetchMyListings();
  }, [user]);

  const fetchMyListings = async () => {
    try {
      const res = await api.get('/my-listings');
      if (res.data.status) {
        setListings(res.data.data);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleMarkAsSold = async (id) => {
    if (!window.confirm('Mark this listing as sold?')) return;
    try {
      const res = await api.post(`/products/${id}/mark-sold`);
      if (res.data.status) {
        fetchMyListings();
      }
    } catch (err) {
      alert('Failed to update status.');
    }
  };

  const handleDeleteListing = async (id) => {
    if (!window.confirm('Are you sure you want to delete this listing?')) return;
    try {
      const res = await api.delete(`/products/${id}`);
      if (res.data.status) {
        fetchMyListings();
      }
    } catch (err) {
      alert('Failed to delete listing.');
    }
  };

  return (
    <div style={{ maxWidth: '1100px', margin: '2rem auto', padding: '0 2rem' }}>
      
      {/* Header Profile Bar */}
      <div style={{ background: 'white', padding: '2rem', borderRadius: '16px', border: '1px solid #e2e8f0', marginBottom: '2rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h1 style={{ fontSize: '1.6rem', fontWeight: 800 }}>Welcome, {user?.name}</h1>
          <p style={{ color: '#64748b', fontSize: '0.95rem' }}>Phone: {user?.phone} | Account Status: Active</p>
          {user?.is_verified && <div style={{ marginTop: '6px' }}><VerifiedBadge text="Verified Seller & Breeder" /></div>}
        </div>

        <Link to="/create-listing" className="btn-primary" style={{ padding: '0.8rem 1.5rem' }}>
          <PlusCircle size={18} /> Create New Listing
        </Link>
      </div>

      {/* Seller Listings List */}
      <div style={{ background: 'white', padding: '2rem', borderRadius: '16px', border: '1px solid #e2e8f0' }}>
        <h2 style={{ fontSize: '1.3rem', fontWeight: 800, marginBottom: '1.5rem' }}>Your Posted Listings ({listings.length})</h2>

        {loading ? (
          <div style={{ textAlign: 'center', padding: '3rem' }}>Loading listings...</div>
        ) : listings.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '3rem' }}>
            <p style={{ color: '#64748b', marginBottom: '1rem' }}>You haven't posted any listings yet.</p>
            <Link to="/create-listing" className="btn-primary">Post Your First Pet or Item</Link>
          </div>
        ) : (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            {listings.map(item => (
              <div key={item.id} style={{ display: 'flex', gap: '1.5rem', padding: '1rem', border: '1px solid #f1f5f9', borderRadius: '12px', alignItems: 'center' }}>
                <img
                  src={item.primary_image || 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=300'}
                  alt={item.title}
                  style={{ width: '90px', height: '90px', objectFit: 'cover', borderRadius: '10px' }}
                />

                <div style={{ flex: 1 }}>
                  <div style={{ display: 'flex', gap: '8px', alignItems: 'center', marginBottom: '4px' }}>
                    <span style={{ fontSize: '0.8rem', color: '#64748b', fontWeight: 600 }}>{item.category?.name}</span>
                    {item.status === 'pending' && <span style={{ background: '#fef3c7', color: '#b45309', fontSize: '0.75rem', fontWeight: 700, padding: '2px 8px', borderRadius: '10px' }}>Pending Admin Approval</span>}
                    {item.status === 'active' && <span style={{ background: '#dcfce7', color: '#15803d', fontSize: '0.75rem', fontWeight: 700, padding: '2px 8px', borderRadius: '10px' }}>Active / Live</span>}
                    {item.status === 'rejected' && <span style={{ background: '#fee2e2', color: '#b91c1c', fontSize: '0.75rem', fontWeight: 700, padding: '2px 8px', borderRadius: '10px' }}>Rejected</span>}
                    {item.status === 'sold' && <span style={{ background: '#e2e8f0', color: '#475569', fontSize: '0.75rem', fontWeight: 700, padding: '2px 8px', borderRadius: '10px' }}>Sold</span>}
                  </div>

                  <h3 style={{ fontSize: '1.1rem', fontWeight: 700, marginBottom: '4px' }}>{item.title}</h3>
                  <div style={{ fontSize: '1.1rem', fontWeight: 800, color: '#059669' }}>
                    {item.price ? `৳ ${Number(item.price).toLocaleString()}` : 'Negotiable'}
                  </div>
                </div>

                <div style={{ display: 'flex', gap: '8px' }}>
                  <Link to={`/product/${item.slug || item.id}`} className="btn-outline" style={{ padding: '0.5rem 0.9rem', fontSize: '0.85rem' }}>
                    <Eye size={14} /> View
                  </Link>
                  {item.status === 'active' && (
                    <button onClick={() => handleMarkAsSold(item.id)} className="btn-outline" style={{ padding: '0.5rem 0.9rem', fontSize: '0.85rem', color: '#059669' }}>
                      <CheckCircle size={14} /> Mark Sold
                    </button>
                  )}
                  <button onClick={() => handleDeleteListing(item.id)} className="btn-outline" style={{ padding: '0.5rem 0.9rem', fontSize: '0.85rem', color: '#ef4444' }}>
                    <Trash2 size={14} /> Delete
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default SellerDashboard;
