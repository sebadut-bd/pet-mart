import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import VerifiedBadge from '../components/VerifiedBadge';
import { MapPin, Phone, Heart, MessageSquare, AlertTriangle, Eye, ShieldCheck, Check } from 'lucide-react';

const ProductDetail = () => {
  const { identifier } = useParams();
  const { user } = useAuth();
  const [product, setProduct] = useState(null);
  const [activeImgIndex, setActiveImgIndex] = useState(0);
  const [isWishlisted, setIsWishlisted] = useState(false);
  const [loading, setLoading] = useState(true);

  // Modals state
  const [showInquiryModal, setShowInquiryModal] = useState(false);
  const [inquiryMsg, setInquiryMsg] = useState('');
  const [inquirySent, setInquirySent] = useState(false);

  const [showReportModal, setShowReportModal] = useState(false);
  const [reportReason, setReportReason] = useState('Spam or Scam');

  useEffect(() => {
    fetchProduct();
  }, [identifier]);

  const fetchProduct = async () => {
    try {
      const res = await api.get(`/products/${identifier}`);
      if (res.data.status) {
        setProduct(res.data.data);
        setIsWishlisted(res.data.data.is_wishlisted);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleWishlistToggle = async () => {
    if (!user) {
      alert('Please login to save listings to your wishlist.');
      return;
    }
    try {
      const res = await api.post('/wishlist/toggle', { product_id: product.id });
      if (res.data.status) {
        setIsWishlisted(res.data.is_wishlisted);
      }
    } catch (err) {
      console.error(err);
    }
  };

  const handleSendInquiry = async (e) => {
    e.preventDefault();
    if (!user) {
      alert('Please login to contact seller.');
      return;
    }
    try {
      const res = await api.post('/inquiries', {
        product_id: product.id,
        message: inquiryMsg,
      });
      if (res.data.status) {
        setInquirySent(true);
        setTimeout(() => {
          setShowInquiryModal(false);
          setInquirySent(false);
          setInquiryMsg('');
        }, 1500);
      }
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to send inquiry.');
    }
  };

  const handleReportListing = async (e) => {
    e.preventDefault();
    if (!user) {
      alert('Please login to report a listing.');
      return;
    }
    try {
      const res = await api.post(`/products/${product.id}/report`, {
        reason: reportReason,
      });
      if (res.data.status) {
        alert('Report submitted to admin for investigation.');
        setShowReportModal(false);
      }
    } catch (err) {
      alert('Failed to report listing.');
    }
  };

  if (loading) return <div style={{ textAlign: 'center', padding: '5rem' }}>Loading listing details...</div>;
  if (!product) return <div style={{ textAlign: 'center', padding: '5rem' }}>Listing not found.</div>;

  return (
    <div style={{ maxWidth: '1200px', margin: '2rem auto', padding: '0 2rem' }}>
      
      {/* Breadcrumb */}
      <div style={{ marginBottom: '1.5rem', color: '#64748b', fontSize: '0.9rem' }}>
        <Link to="/" style={{ color: '#10b981' }}>Home</Link> / <Link to="/browse" style={{ color: '#10b981' }}>{product.category?.name}</Link> / {product.title}
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: '1fr 380px', gap: '2.5rem' }}>
        
        {/* Main Content (Images & Specs) */}
        <div>
          {/* Gallery */}
          <div style={{ background: 'white', borderRadius: '16px', overflow: 'hidden', border: '1px solid #e2e8f0', marginBottom: '2rem' }}>
            <div style={{ width: '100%', height: '420px', background: '#0f172a' }}>
              <img
                src={product.images?.[activeImgIndex]?.image_path || 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=800'}
                alt={product.title}
                style={{ width: '100%', height: '100%', objectFit: 'contain' }}
              />
            </div>
            
            {product.images && product.images.length > 1 && (
              <div style={{ display: 'flex', gap: '10px', padding: '1rem', overflowX: 'auto', background: '#f8fafc' }}>
                {product.images.map((img, idx) => (
                  <img
                    key={img.id}
                    src={img.image_path}
                    alt="Thumbnail"
                    onClick={() => setActiveImgIndex(idx)}
                    style={{
                      width: '70px',
                      height: '70px',
                      objectFit: 'cover',
                      borderRadius: '8px',
                      cursor: 'pointer',
                      border: activeImgIndex === idx ? '2px solid #10b981' : '1px solid #cbd5e1'
                    }}
                  />
                ))}
              </div>
            )}
          </div>

          {/* Title & Price */}
          <div style={{ background: 'white', padding: '2rem', borderRadius: '16px', border: '1px solid #e2e8f0', marginBottom: '2rem' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span className="badge-featured" style={{ position: 'static' }}>{product.listing_type}</span>
              <button
                onClick={handleWishlistToggle}
                style={{
                  background: isWishlisted ? '#fef2f2' : '#f1f5f9',
                  color: isWishlisted ? '#ef4444' : '#64748b',
                  border: 'none',
                  padding: '8px 16px',
                  borderRadius: '20px',
                  cursor: 'pointer',
                  fontWeight: 600,
                  display: 'flex',
                  alignItems: 'center',
                  gap: '6px'
                }}
              >
                <Heart size={16} fill={isWishlisted ? '#ef4444' : 'none'} />
                {isWishlisted ? 'Saved in Wishlist' : 'Save Listing'}
              </button>
            </div>

            <h1 style={{ fontSize: '1.8rem', fontWeight: 800, margin: '1rem 0 0.5rem' }}>{product.title}</h1>

            <div style={{ fontSize: '2rem', fontWeight: 800, color: '#059669', marginBottom: '1rem' }}>
              {product.price ? `৳ ${Number(product.price).toLocaleString()}` : 'Negotiable'}
              {product.is_negotiable && <span style={{ fontSize: '0.9rem', color: '#64748b', marginLeft: '8px' }}>(Negotiable)</span>}
            </div>

            <div style={{ display: 'flex', gap: '1.5rem', color: '#64748b', fontSize: '0.9rem', borderTop: '1px solid #f1f5f9', paddingTop: '1rem' }}>
              <span style={{ display: 'flex', alignItems: 'center', gap: '4px' }}><MapPin size={16} /> {product.location?.full_address || product.location?.district_name || 'Bangladesh'}</span>
              <span style={{ display: 'flex', alignItems: 'center', gap: '4px' }}><Eye size={16} /> {product.views} views</span>
              <span>Posted: {new Date(product.created_at).toLocaleDateString()}</span>
            </div>
          </div>

          {/* Dynamic Specifications Table (Breed, Age, Vaccination, etc.) */}
          {product.attributes && Object.keys(product.attributes).length > 0 && (
            <div style={{ background: 'white', padding: '2rem', borderRadius: '16px', border: '1px solid #e2e8f0', marginBottom: '2rem' }}>
              <h3 style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '1rem' }}>Pet & Item Specifications</h3>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(220px, 1fr))', gap: '1rem' }}>
                {Object.entries(product.attributes).map(([key, value]) => (
                  <div key={key} style={{ background: '#f8fafc', padding: '1rem', borderRadius: '10px', border: '1px solid #f1f5f9' }}>
                    <div style={{ fontSize: '0.8rem', color: '#64748b', textTransform: 'capitalize' }}>{key.replace('_', ' ')}</div>
                    <div style={{ fontSize: '1rem', fontWeight: 700, marginTop: '2px', color: '#0f172a' }}>{value}</div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Description */}
          <div style={{ background: 'white', padding: '2rem', borderRadius: '16px', border: '1px solid #e2e8f0' }}>
            <h3 style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '1rem' }}>Description</h3>
            <p style={{ lineHeight: 1.8, color: '#334155', whiteSpace: 'pre-line' }}>{product.description}</p>
          </div>
        </div>

        {/* Sidebar (Seller Profile & Actions) */}
        <div>
          <div style={{ background: 'white', padding: '2rem', borderRadius: '16px', border: '1px solid #e2e8f0', position: 'sticky', top: '90px' }}>
            <div style={{ textAlign: 'center', marginBottom: '1.5rem' }}>
              <img
                src={product.seller?.image || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150'}
                alt={product.seller?.name}
                style={{ width: '80px', height: '80px', borderRadius: '50%', objectFit: 'cover', marginBottom: '0.75rem', border: '2px solid #10b981' }}
              />
              <h3 style={{ fontSize: '1.2rem', fontWeight: 700 }}>{product.seller?.name}</h3>
              {product.seller?.is_verified ? (
                <div style={{ marginTop: '4px' }}><VerifiedBadge text="Verified Seller & Breeder" /></div>
              ) : (
                <span style={{ fontSize: '0.8rem', color: '#64748b' }}>Marketplace Member</span>
              )}
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <a
                href={`tel:${product.seller?.phone}`}
                className="btn-primary"
                style={{ width: '100%', justifyContent: 'center', padding: '0.9rem' }}
              >
                <Phone size={18} /> Call {product.seller?.phone}
              </a>

              <button
                onClick={() => setShowInquiryModal(true)}
                className="btn-outline"
                style={{ width: '100%', justifyContent: 'center', padding: '0.9rem', display: 'flex', alignItems: 'center', gap: '6px' }}
              >
                <MessageSquare size={18} /> Send Private Message
              </button>

              <button
                onClick={() => setShowReportModal(true)}
                style={{ background: 'none', border: 'none', color: '#ef4444', cursor: 'pointer', fontSize: '0.85rem', display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '4px', marginTop: '0.5rem' }}
              >
                <AlertTriangle size={14} /> Report Abuse or Scam
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Inquiry Message Modal */}
      {showInquiryModal && (
        <div className="modal-overlay">
          <div className="modal-card">
            <h3 style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '1rem' }}>Contact Seller about {product.title}</h3>
            {inquirySent ? (
              <div style={{ textAlign: 'center', padding: '2rem', color: '#059669', fontWeight: 700, display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px' }}>
                <Check size={24} /> Message sent successfully!
              </div>
            ) : (
              <form onSubmit={handleSendInquiry}>
                <div className="form-group">
                  <label>Your Message</label>
                  <textarea
                    rows={4}
                    required
                    value={inquiryMsg}
                    onChange={(e) => setInquiryMsg(e.target.value)}
                    placeholder="Ask about vaccination documents, delivery, viewing time, or price negotiation..."
                    className="form-control"
                  ></textarea>
                </div>
                <div style={{ display: 'flex', gap: '1rem', justifyContent: 'flex-end' }}>
                  <button type="button" onClick={() => setShowInquiryModal(false)} className="btn-outline">Cancel</button>
                  <button type="submit" className="btn-primary">Send Inquiry</button>
                </div>
              </form>
            )}
          </div>
        </div>
      )}

      {/* Report Modal */}
      {showReportModal && (
        <div className="modal-overlay">
          <div className="modal-card">
            <h3 style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '1rem', color: '#ef4444' }}>Report Listing</h3>
            <form onSubmit={handleReportListing}>
              <div className="form-group">
                <label>Reason for Report</label>
                <select value={reportReason} onChange={(e) => setReportReason(e.target.value)} className="form-control">
                  <option value="Spam or Scam">Spam or Scam</option>
                  <option value="Fake photos or misleading breed">Fake photos or misleading breed</option>
                  <option value="Animal welfare concern">Animal welfare concern</option>
                  <option value="Prohibited item or breed">Prohibited item or breed</option>
                </select>
              </div>
              <div style={{ display: 'flex', gap: '1rem', justifyContent: 'flex-end', marginTop: '1.5rem' }}>
                <button type="button" onClick={() => setShowReportModal(false)} className="btn-outline">Cancel</button>
                <button type="submit" className="btn-primary" style={{ background: '#ef4444' }}>Submit Report</button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
};

export default ProductDetail;
