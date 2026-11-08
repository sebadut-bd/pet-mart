import React from 'react';
import { Routes, Route } from 'react-router-dom';
import Navbar from './components/Navbar';
import Footer from './components/Footer';
import Home from './pages/Home';
import CategoryBrowse from './pages/CategoryBrowse';
import ProductDetail from './pages/ProductDetail';
import CreateListing from './pages/CreateListing';
import Login from './pages/Login';
import Register from './pages/Register';
import SellerDashboard from './pages/SellerDashboard';
import Wishlist from './pages/Wishlist';
import Inquiries from './pages/Inquiries';

function App() {
  return (
    <div style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}>
      <Navbar />
      <main style={{ flex: 1 }}>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/browse" element={<CategoryBrowse />} />
          <Route path="/product/:identifier" element={<ProductDetail />} />
          <Route path="/create-listing" element={<CreateListing />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/dashboard" element={<SellerDashboard />} />
          <Route path="/wishlist" element={<Wishlist />} />
          <Route path="/inquiries" element={<Inquiries />} />
        </Routes>
      </main>
      <Footer />
    </div>
  );
}

export default App;
