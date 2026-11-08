import React, { useEffect, useState } from 'react';
import api from '../services/api';
import { MessageSquare, User, Clock } from 'lucide-react';

const Inquiries = () => {
  const [inquiries, setInquiries] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchInquiries();
  }, []);

  const fetchInquiries = async () => {
    try {
      const res = await api.get('/inquiries');
      if (res.data.status) {
        setInquiries(res.data.data);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ maxWidth: '900px', margin: '2rem auto', padding: '0 2rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '1.5rem' }}>
        <MessageSquare size={28} color="#10b981" />
        <div>
          <h1 style={{ fontSize: '1.6rem', fontWeight: 800 }}>Inquiries & Messages ({inquiries.length})</h1>
          <p style={{ color: '#64748b' }}>Messages exchanged regarding marketplace pets & items</p>
        </div>
      </div>

      {loading ? (
        <div style={{ textAlign: 'center', padding: '4rem' }}>Loading messages...</div>
      ) : inquiries.length === 0 ? (
        <div style={{ background: 'white', padding: '4rem', textAlign: 'center', borderRadius: '16px', border: '1px solid #e2e8f0' }}>
          <h3>No inquiries found</h3>
          <p style={{ color: '#64748b', marginTop: '6px' }}>When buyers message you about a listing, conversation threads will appear here.</p>
        </div>
      ) : (
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          {inquiries.map(inq => (
            <div key={inq.id} style={{ background: 'white', padding: '1.5rem', borderRadius: '14px', border: '1px solid #e2e8f0' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.75rem' }}>
                <span style={{ fontSize: '0.9rem', fontWeight: 700, color: '#10b981' }}>
                  Listing: {inq.product_title}
                </span>
                <span style={{ fontSize: '0.8rem', color: '#64748b', display: 'flex', alignItems: 'center', gap: '4px' }}>
                  <Clock size={12} /> {new Date(inq.created_at).toLocaleString()}
                </span>
              </div>
              <div style={{ fontSize: '0.95rem', fontWeight: 600, color: '#0f172a', marginBottom: '0.5rem', display: 'flex', alignItems: 'center', gap: '6px' }}>
                <User size={16} /> {inq.sender_name} → {inq.receiver_name}
              </div>
              <p style={{ background: '#f8fafc', padding: '1rem', borderRadius: '8px', color: '#334155', fontSize: '0.95rem' }}>
                {inq.message}
              </p>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default Inquiries;
