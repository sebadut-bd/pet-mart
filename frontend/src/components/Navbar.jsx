import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { PawPrint, PlusCircle, Heart, MessageSquare, User, LogOut } from 'lucide-react';

const Navbar = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  return (
    <nav className="navbar">
      <Link to="/" className="logo">
        <PawPrint size={30} />
        Pet<span>Mart</span>
      </Link>

      <div className="nav-links">
        <Link to="/" style={{ fontWeight: 600 }}>Home</Link>
        <Link to="/browse" style={{ fontWeight: 600 }}>Categories</Link>

        {user ? (
          <>
            <Link to="/wishlist" style={{ display: 'flex', alignItems: 'center', gap: '4px', fontWeight: 600 }}>
              <Heart size={18} /> Saved
            </Link>
            <Link to="/inquiries" style={{ display: 'flex', alignItems: 'center', gap: '4px', fontWeight: 600 }}>
              <MessageSquare size={18} /> Inquiries
            </Link>
            <Link to="/dashboard" style={{ display: 'flex', alignItems: 'center', gap: '4px', fontWeight: 600 }}>
              <User size={18} /> {user.name.split(' ')[0]}
            </Link>
            <Link to="/create-listing" className="btn-primary">
              <PlusCircle size={18} /> Sell Pet / Item
            </Link>
            <button onClick={logout} className="btn-outline" style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
              <LogOut size={16} /> Logout
            </button>
          </>
        ) : (
          <>
            <Link to="/login" className="btn-outline">Login</Link>
            <Link to="/register" className="btn-primary">Register</Link>
          </>
        )}
      </div>
    </nav>
  );
};

export default Navbar;
