import React, { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '../services/api';
import ProductCard from '../components/ProductCard';
import { Filter, Search, RotateCcw } from 'lucide-react';

const CategoryBrowse = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const [categories, setCategories] = useState([]);
  const [selectedCategory, setSelectedCategory] = useState(null);
  const [products, setProducts] = useState([]);
  const [locations, setLocations] = useState({ divisions: [], districts: [] });
  const [loading, setLoading] = useState(true);

  // Filters state
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '');
  const [categorySlug, setCategorySlug] = useState(searchParams.get('category_slug') || '');
  const [listingType, setListingType] = useState(searchParams.get('listing_type') || '');
  const [divisionId, setDivisionId] = useState('');
  const [minPrice, setMinPrice] = useState('');
  const [maxPrice, setMaxPrice] = useState('');
  const [attrFilter, setAttrFilter] = useState({});

  useEffect(() => {
    fetchInitialData();
  }, []);

  useEffect(() => {
    fetchProducts();
  }, [categorySlug, listingType, divisionId]);

  const fetchInitialData = async () => {
    try {
      const [catRes, locRes] = await Promise.all([
        api.get('/categories'),
        api.get('/locations')
      ]);
      if (catRes.data.status) {
        setCategories(catRes.data.data);
        if (categorySlug) {
          const found = catRes.data.data.find(c => c.slug === categorySlug);
          setSelectedCategory(found);
        }
      }
      if (locRes.data.status) {
        setLocations(locRes.data.data);
      }
    } catch (err) {
      console.error(err);
    }
  };

  const fetchProducts = async () => {
    setLoading(true);
    try {
      let params = {
        search: searchTerm,
        category_slug: categorySlug,
        listing_type: listingType,
        division_id: divisionId,
        min_price: minPrice,
        max_price: maxPrice,
      };

      // Add dynamic attribute query parameters
      Object.keys(attrFilter).forEach(k => {
        if (attrFilter[k]) {
          params[`attributes[${k}]`] = attrFilter[k];
        }
      });

      const res = await api.get('/products', { params });
      if (res.data.status) {
        setProducts(res.data.data);
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleCategoryChange = (slug) => {
    setCategorySlug(slug);
    const cat = categories.find(c => c.slug === slug);
    setSelectedCategory(cat);
    setAttrFilter({});
  };

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    fetchProducts();
  };

  const resetFilters = () => {
    setSearchTerm('');
    setCategorySlug('');
    setSelectedCategory(null);
    setListingType('');
    setDivisionId('');
    setMinPrice('');
    setMaxPrice('');
    setAttrFilter({});
    setSearchParams({});
    fetchProducts();
  };

  return (
    <div style={{ maxWidth: '1280px', margin: '2rem auto', padding: '0 2rem' }}>
      <div style={{ display: 'grid', gridTemplateColumns: '280px 1fr', gap: '2rem' }}>
        
        {/* Sidebar Filters */}
        <aside style={{ background: 'white', padding: '1.5rem', borderRadius: '16px', border: '1px solid #e2e8f0', height: 'fit-content' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.25rem' }}>
            <h3 style={{ fontSize: '1.1rem', fontWeight: 700, display: 'flex', alignItems: 'center', gap: '6px' }}>
              <Filter size={18} /> Filters
            </h3>
            <button onClick={resetFilters} style={{ background: 'none', border: 'none', color: '#10b981', cursor: 'pointer', fontSize: '0.85rem', display: 'flex', alignItems: 'center', gap: '4px' }}>
              <RotateCcw size={14} /> Reset
            </button>
          </div>

          {/* Listing Type Filter Tabs */}
          <div className="form-group">
            <label>Listing Purpose</label>
            <select value={listingType} onChange={(e) => setListingType(e.target.value)} className="form-control">
              <option value="">All Purpose</option>
              <option value="sell">For Sale</option>
              <option value="adopt">Adoption (Free/Fee)</option>
              <option value="stud_service">Stud Service</option>
              <option value="lost">Lost Pet</option>
              <option value="found">Found Pet</option>
            </select>
          </div>

          {/* Category Filter */}
          <div className="form-group">
            <label>Category</label>
            <select value={categorySlug} onChange={(e) => handleCategoryChange(e.target.value)} className="form-control">
              <option value="">All Categories</option>
              {categories.map(c => (
                <option key={c.id} value={c.slug}>{c.name}</option>
              ))}
            </select>
          </div>

          {/* Dynamic Attribute Filter (e.g. Breed, Vaccination) */}
          {selectedCategory && selectedCategory.attributes && selectedCategory.attributes.length > 0 && (
            <div style={{ background: '#ecfdf5', padding: '1rem', borderRadius: '10px', marginBottom: '1.25rem' }}>
              <h4 style={{ fontSize: '0.9rem', fontWeight: 700, color: '#047857', marginBottom: '0.75rem' }}>
                {selectedCategory.name} Specifications
              </h4>
              {selectedCategory.attributes.map(attr => (
                <div key={attr.id} className="form-group">
                  <label>{attr.name}</label>
                  {attr.type === 'select' && attr.options ? (
                    <select
                      value={attrFilter[attr.attribute_key] || ''}
                      onChange={(e) => setAttrFilter({ ...attrFilter, [attr.attribute_key]: e.target.value })}
                      className="form-control"
                    >
                      <option value="">Any {attr.name}</option>
                      {attr.options.map(opt => (
                        <option key={opt} value={opt}>{opt}</option>
                      ))}
                    </select>
                  ) : (
                    <input
                      type="text"
                      placeholder={`Filter by ${attr.name}`}
                      value={attrFilter[attr.attribute_key] || ''}
                      onChange={(e) => setAttrFilter({ ...attrFilter, [attr.attribute_key]: e.target.value })}
                      className="form-control"
                    />
                  )}
                </div>
              ))}
            </div>
          )}

          {/* Location Filter */}
          <div className="form-group">
            <label>Division Location</label>
            <select value={divisionId} onChange={(e) => setDivisionId(e.target.value)} className="form-control">
              <option value="">All Bangladesh</option>
              {locations.divisions.map(div => (
                <option key={div.id} value={div.id}>{div.name}</option>
              ))}
            </select>
          </div>

          {/* Price Range */}
          <div className="form-group">
            <label>Price Range (৳)</label>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px' }}>
              <input
                type="number"
                placeholder="Min"
                value={minPrice}
                onChange={(e) => setMinPrice(e.target.value)}
                className="form-control"
              />
              <input
                type="number"
                placeholder="Max"
                value={maxPrice}
                onChange={(e) => setMaxPrice(e.target.value)}
                className="form-control"
              />
            </div>
          </div>

          <button onClick={fetchProducts} className="btn-primary" style={{ width: '100%', justifyContent: 'center' }}>
            Apply Filter
          </button>
        </aside>

        {/* Product Listings Results */}
        <main>
          <div style={{ marginBottom: '1.5rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h2 style={{ fontSize: '1.4rem', fontWeight: 800 }}>
              Marketplace Listings ({products.length})
            </h2>

            <form onSubmit={handleSearchSubmit} style={{ display: 'flex', gap: '8px' }}>
              <input
                type="text"
                placeholder="Search..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="form-control"
                style={{ width: '220px' }}
              />
              <button type="submit" className="btn-outline">Search</button>
            </form>
          </div>

          {loading ? (
            <div style={{ textAlign: 'center', padding: '4rem' }}>Loading listings...</div>
          ) : products.length === 0 ? (
            <div style={{ background: 'white', padding: '4rem', textAlign: 'center', borderRadius: '16px' }}>
              <h3>No listings found matching your criteria</h3>
              <p style={{ color: '#64748b', marginTop: '8px' }}>Try resetting filters or expanding search location.</p>
            </div>
          ) : (
            <div className="product-grid">
              {products.map(prod => (
                <ProductCard key={prod.id} product={prod} />
              ))}
            </div>
          )}
        </main>
      </div>
    </div>
  );
};

export default CategoryBrowse;
