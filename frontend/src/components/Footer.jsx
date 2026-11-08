import React from 'react';
import { PawPrint, Shield, Phone, Mail } from 'lucide-react';

const Footer = () => {
  return (
    <footer style={{ background: '#0f172a', color: '#94a3b8', padding: '3rem 2rem 1.5rem', marginTop: '4rem' }}>
      <div style={{ maxWidth: '1280px', margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '2rem' }}>
        <div>
          <div style={{ color: '#10b981', fontSize: '1.4rem', fontWeight: 800, display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '1rem' }}>
            <PawPrint size={26} /> PetMart Bangladesh
          </div>
          <p style={{ fontSize: '0.9rem', lineHeight: 1.6 }}>
            The trusted Bangladesh marketplace for verified pet breeders, adoption, animal care products, and secondhand pet supplies.
          </p>
        </div>

        <div>
          <h4 style={{ color: '#f8fafc', marginBottom: '1rem' }}>Quick Links</h4>
          <ul style={{ listStyle: 'none', lineHeight: 2, fontSize: '0.9rem' }}>
            <li><a href="/browse">Browse Dogs & Puppies</a></li>
            <li><a href="/browse">Browse Cats & Kittens</a></li>
            <li><a href="/browse">Birds & Aquatics</a></li>
            <li><a href="/browse">Pet Food & Accessories</a></li>
          </ul>
        </div>

        <div>
          <h4 style={{ color: '#f8fafc', marginBottom: '1rem' }}>Safety & Trust</h4>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '0.75rem', fontSize: '0.9rem' }}>
            <Shield color="#10b981" size={20} /> Verified Breeder Program
          </div>
          <p style={{ fontSize: '0.85rem' }}>Every animal listing goes through strict admin moderation before publication.</p>
        </div>

        <div>
          <h4 style={{ color: '#f8fafc', marginBottom: '1rem' }}>Contact Support</h4>
          <p style={{ fontSize: '0.9rem', display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '0.5rem' }}>
            <Phone size={16} /> +880 1700-000000
          </p>
          <p style={{ fontSize: '0.9rem', display: 'flex', alignItems: 'center', gap: '8px' }}>
            <Mail size={16} /> support@petmart.com.bd
          </p>
        </div>
      </div>

      <div style={{ borderTop: '1px solid #334155', marginTop: '2.5rem', paddingTop: '1.5rem', textAlign: 'center', fontSize: '0.85rem' }}>
        © {new Date().getFullYear()} PetMart Bangladesh. All rights reserved. Moderated classifieds platform.
      </div>
    </footer>
  );
};

export default Footer;
