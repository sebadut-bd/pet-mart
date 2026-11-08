import React from 'react';
import { CheckCircle } from 'lucide-react';

const VerifiedBadge = ({ text = "Verified Seller" }) => {
  return (
    <span style={{
      display: 'inline-flex',
      alignItems: 'center',
      gap: '4px',
      background: '#dcfce7',
      color: '#15803d',
      fontSize: '0.75rem',
      fontWeight: '700',
      padding: '2px 8px',
      borderRadius: '12px'
    }}>
      <CheckCircle size={13} color="#15803d" />
      {text}
    </span>
  );
};

export default VerifiedBadge;
