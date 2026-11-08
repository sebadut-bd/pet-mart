import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import { PlusCircle, Image, Sparkles } from 'lucide-react';

const CreateListing = () => {
  const { user } = useAuth();
  const navigate = useNavigate();

  const [categories, setCategories] = useState([]);
  const [locations, setLocations] = useState({ divisions: [], districts: [] });
  const [selectedCategory, setSelectedCategory] = useState(null);

  // Form State
  const [title, setTitle] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [listingType, setListingType] = useState('sell');
  const [price, setPrice] = useState('');
  const [isNegotiable, setIsNegotiable] = useState(false);
  const [isFree, setIsFree] = useState(false);
  const [divisionId, setDivisionId] = useState('');
  const [districtId, setDistrictId] = useState('');
  const [fullAddress, setFullAddress] = useState('');
  const [description, setDescription] = useState('');
  const [imageUrls, setImageUrls] = useState(['', '', '']);
  const [attrValues, setAttrValues] = useState({});

  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  useEffect(() => {
    if (!user) {
      navigate('/login');
      return;
    }
    fetchMetaData();
  }, [user]);

  const fetchMetaData = async () => {
    try {
      const [catRes, locRes] = await Promise.all([
        api.get('/categories'),
        api.get('/locations')
      ]);
      if (catRes.data.status) setCategories(catRes.data.data);
      if (locRes.data.status) setLocations(locRes.data.data);
    } catch (err) {
      console.error(err);
    }
  };

  const handleCategorySelect = (id) => {
    setCategoryId(id);
    const found = categories.find(c => c.id === parseInt(id));
    setSelectedCategory(found);
    setAttrValues({});
  };

  const handleImageUrlChange = (index, value) => {
    const updated = [...imageUrls];
    updated[index] = value;
    setImageUrls(updated);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrorMsg('');

    const filteredImages = imageUrls.filter(url => url.trim() !== '');

    const payload = {
      title,
      category_id: categoryId,
      listing_type: listingType,
      price: isFree ? 0 : price,
      is_negotiable: isNegotiable,
      is_free: isFree,
      division_id: divisionId,
      district_id: districtId,
      full_address: fullAddress,
      description,
      images: filteredImages.length > 0 ? filteredImages : ['https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=800'],
      attributes: attrValues,
    };

    try {
      const res = await api.post('/products', payload);
      if (res.data.status) {
        alert(res.data.message);
        navigate('/dashboard');
      }
    } catch (err) {
      setErrorMsg(err.response?.data?.message || 'Failed to submit listing.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div style={{ maxWidth: '800px', margin: '2rem auto', padding: '0 2rem' }}>
      <div style={{ background: 'white', padding: '2.5rem', borderRadius: '20px', border: '1px solid #e2e8f0', boxShadow: '0 10px 30px rgba(0,0,0,0.05)' }}>
        
        <div style={{ display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '1.5rem' }}>
          <PlusCircle size={28} color="#10b981" />
          <div>
            <h1 style={{ fontSize: '1.6rem', fontWeight: 800 }}>Create Pet or Goods Listing</h1>
            <p style={{ color: '#64748b', fontSize: '0.9rem' }}>Listings will be moderated by admin prior to public listing.</p>
          </div>
        </div>

        {errorMsg && (
          <div style={{ background: '#fef2f2', color: '#dc2626', padding: '1rem', borderRadius: '8px', marginBottom: '1.5rem', fontWeight: 600 }}>
            {errorMsg}
          </div>
        )}

        <form onSubmit={handleSubmit}>
          
          {/* Title & Category */}
          <div className="form-group">
            <label>Listing Title *</label>
            <input
              type="text"
              required
              placeholder="e.g. Purebred Persian Kitten 2 Months Old Fully Vaccinated"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="form-control"
            />
          </div>

          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
            <div className="form-group">
              <label>Marketplace Category *</label>
              <select required value={categoryId} onChange={(e) => handleCategorySelect(e.target.value)} className="form-control">
                <option value="">Select Category</option>
                {categories.map(cat => (
                  <option key={cat.id} value={cat.id}>{cat.name}</option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Listing Purpose *</label>
              <select value={listingType} onChange={(e) => setListingType(e.target.value)} className="form-control">
                <option value="sell">For Sale</option>
                <option value="adopt">Adoption</option>
                <option value="stud_service">Stud Service</option>
                <option value="lost">Lost Pet</option>
                <option value="found">Found Pet</option>
              </select>
            </div>
          </div>

          {/* Dynamic Specifications Render */}
          {selectedCategory && selectedCategory.attributes && selectedCategory.attributes.length > 0 && (
            <div style={{ background: '#ecfdf5', padding: '1.25rem', borderRadius: '12px', marginBottom: '1.5rem', border: '1px dashed #34d399' }}>
              <h4 style={{ fontSize: '0.95rem', fontWeight: 700, color: '#047857', marginBottom: '1rem', display: 'flex', alignItems: 'center', gap: '6px' }}>
                <Sparkles size={16} /> Category Specific Fields ({selectedCategory.name})
              </h4>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
                {selectedCategory.attributes.map(attr => (
                  <div key={attr.id} className="form-group" style={{ marginBottom: 0 }}>
                    <label>{attr.name} {attr.is_required ? '*' : ''}</label>
                    {attr.type === 'select' && attr.options ? (
                      <select
                        required={attr.is_required}
                        value={attrValues[attr.attribute_key] || ''}
                        onChange={(e) => setAttrValues({ ...attrValues, [attr.attribute_key]: e.target.value })}
                        className="form-control"
                      >
                        <option value="">Select {attr.name}</option>
                        {attr.options.map(opt => (
                          <option key={opt} value={opt}>{opt}</option>
                        ))}
                      </select>
                    ) : (
                      <input
                        type={attr.type === 'number' ? 'number' : 'text'}
                        required={attr.is_required}
                        placeholder={`Enter ${attr.name}`}
                        value={attrValues[attr.attribute_key] || ''}
                        onChange={(e) => setAttrValues({ ...attrValues, [attr.attribute_key]: e.target.value })}
                        className="form-control"
                      />
                    )}
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Price Options */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
            <div className="form-group">
              <label>Price (৳)</label>
              <input
                type="number"
                disabled={isFree}
                placeholder="e.g. 15000"
                value={price}
                onChange={(e) => setPrice(e.target.value)}
                className="form-control"
              />
            </div>
            <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '1.5rem', marginTop: '1.8rem' }}>
              <label style={{ display: 'flex', alignItems: 'center', gap: '6px', cursor: 'pointer', marginBottom: 0 }}>
                <input type="checkbox" checked={isNegotiable} onChange={(e) => setIsNegotiable(e.target.checked)} /> Negotiable
              </label>
              <label style={{ display: 'flex', alignItems: 'center', gap: '6px', cursor: 'pointer', marginBottom: 0 }}>
                <input type="checkbox" checked={isFree} onChange={(e) => setIsFree(e.target.checked)} /> Free / Adoption
              </label>
            </div>
          </div>

          {/* Location */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
            <div className="form-group">
              <label>Division *</label>
              <select required value={divisionId} onChange={(e) => setDivisionId(e.target.value)} className="form-control">
                <option value="">Select Division</option>
                {locations.divisions.map(div => (
                  <option key={div.id} value={div.id}>{div.name}</option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Area / Address *</label>
              <input
                type="text"
                required
                placeholder="e.g. Uttara Sector 4, Dhaka"
                value={fullAddress}
                onChange={(e) => setFullAddress(e.target.value)}
                className="form-control"
              />
            </div>
          </div>

          {/* Photos */}
          <div className="form-group">
            <label style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
              <Image size={16} /> Photo URLs (Provide up to 3 direct image links)
            </label>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
              {imageUrls.map((url, idx) => (
                <input
                  key={idx}
                  type="url"
                  placeholder={`Photo URL ${idx + 1} (e.g. https://images.unsplash.com/...)`}
                  value={url}
                  onChange={(e) => handleImageUrlChange(idx, e.target.value)}
                  className="form-control"
                />
              ))}
            </div>
          </div>

          {/* Description */}
          <div className="form-group">
            <label>Detailed Description *</label>
            <textarea
              required
              rows={4}
              placeholder="Describe pet temperament, health history, diet, reason for listing, or item condition..."
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              className="form-control"
            ></textarea>
          </div>

          <button type="submit" disabled={submitting} className="btn-primary" style={{ width: '100%', justifyContent: 'center', padding: '1rem', fontSize: '1rem' }}>
            {submitting ? 'Submitting for Admin Review...' : 'Submit Listing for Moderation'}
          </button>
        </form>
      </div>
    </div>
  );
};

export default CreateListing;
