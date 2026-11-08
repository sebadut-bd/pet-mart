import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api from '../services/api';
import ProductCard from '../components/ProductCard';
import { Search, PawPrint, ShieldCheck, Heart, Dog, Cat, Bird, Fish, Sparkles } from 'lucide-react';

const Home = () => {
  const [categories, setCategories] = useState([]);
  const [products, setProducts] = useState([]);
  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      const [catRes, prodRes] = await Promise.all([
        api.get('/categories'),
        api.get('/products?per_page=8')
      ]);
      if (catRes.data.status) setCategories(catRes.data.data);
      if (prodRes.data.status) setProducts(prodRes.data.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (search.trim()) {
      navigate(`/browse?search=${encodeURIComponent(search)}`);
    }
  };

  const getCategoryIcon = (slug) => {
    if (slug.includes('dog')) return <Dog size={24} />;
    if (slug.includes('cat')) return <Cat size={24} />;
    if (slug.includes('bird')) return <Bird size={24} />;
    if (slug.includes('fish') || slug.includes('aquarium')) return <Fish size={24} />;
    return <PawPrint size={24} />;
  };

  return (
    <div>
      {/* Hero Banner Section */}
      <section style={{
        background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
        color: 'white',
        padding: '4rem 2rem',
        textAlign: 'center',
        position: 'relative',
        overflow: 'hidden'
      }}>
        <div style={{ maxWidth: '800px', margin: '0 auto', position: 'relative', zIndex: 2 }}>
          <span style={{
            background: 'rgba(16, 185, 129, 0.2)',
            color: '#34d399',
            padding: '6px 16px',
            borderRadius: '20px',
            fontSize: '0.85rem',
            fontWeight: 700,
            display: 'inline-flex',
            alignItems: 'center',
            gap: '6px',
            marginBottom: '1rem'
          }}>
            <Sparkles size={16} /> Verified Pet & Goods Marketplace Bangladesh
          </span>
          <h1 style={{ fontSize: '2.5rem', fontWeight: 800, marginBottom: '1rem', lineHeight: 1.2 }}>
            Find & Sell Pets, Animals & Supplies Safely
          </h1>
          <p style={{ color: '#94a3b8', fontSize: '1.1rem', marginBottom: '2rem' }}>
            Browse verified breeders, adoption posts, stud services, and quality pet accessories across Bangladesh.
          </p>

          <form onSubmit={handleSearchSubmit} style={{
            display: 'flex',
            background: 'white',
            borderRadius: '14px',
            padding: '6px',
            boxShadow: '0 10px 25px rgba(0,0,0,0.3)',
            maxWidth: '650px',
            margin: '0 auto'
          }}>
            <input
              type="text"
              placeholder="Search dogs, kittens, breeds, cages, food..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              style={{
                flex: 1,
                border: 'none',
                padding: '0.9rem 1.2rem',
                fontSize: '1rem',
                outline: 'none',
                color: '#0f172a'
              }}
            />
            <button type="submit" className="btn-primary" style={{ padding: '0.9rem 1.8rem' }}>
              <Search size={18} /> Search
            </button>
          </form>
        </div>
      </section>

      {/* Categories Grid */}
      <section className="category-section">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div>
            <h2 style={{ fontSize: '1.5rem', fontWeight: 800 }}>Explore Categories</h2>
            <p style={{ color: '#64748b' }}>Select category to filter breeds and attributes</p>
          </div>
          <Link to="/browse" className="btn-outline" style={{ fontSize: '0.9rem' }}>View All Categories</Link>
        </div>

        <div className="category-grid">
          {categories.map((cat) => (
            <Link key={cat.id} to={`/browse?category_slug=${cat.slug}`} className="category-card">
              <div className="category-icon">
                {getCategoryIcon(cat.slug)}
              </div>
              <h3 style={{ fontSize: '1rem', fontWeight: 700, marginBottom: '4px' }}>{cat.name}</h3>
              <span style={{ fontSize: '0.8rem', color: '#64748b' }}>{cat.children?.length || 0} Sub-categories</span>
            </Link>
          ))}
        </div>
      </section>

      {/* Featured & Recent Listings */}
      <section style={{ maxWidth: '1280px', margin: '2rem auto', padding: '0 2rem' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem' }}>
          <div>
            <h2 style={{ fontSize: '1.5rem', fontWeight: 800 }}>Recent Verified Listings</h2>
            <p style={{ color: '#64748b' }}>Freshly approved pets and secondhand supplies</p>
          </div>
          <Link to="/browse" className="btn-primary">Browse All ({products.length})</Link>
        </div>

        {loading ? (
          <div style={{ textAlign: 'center', padding: '3rem' }}>Loading marketplace listings...</div>
        ) : (
          <div className="product-grid">
            {products.map((prod) => (
              <ProductCard key={prod.id} product={prod} />
            ))}
          </div>
        )}
      </section>
    </div>
  );
};

export default Home;
