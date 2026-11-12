import React, { useEffect, useState } from 'react';
import { LISTING_TYPES } from '../../lib/constants';

/**
 * Controlled filter form. `values` holds string values keyed by URL param name.
 * `onChange(partial)` is called with the changed keys.
 * Price inputs are committed on blur / Enter so typing does not trigger a fetch per key.
 */
export function FilterPanel({ values, onChange, categories = [], divisions = [], idPrefix = 'f' }) {
  const [minPrice, setMinPrice] = useState(values.min_price || '');
  const [maxPrice, setMaxPrice] = useState(values.max_price || '');

  useEffect(() => setMinPrice(values.min_price || ''), [values.min_price]);
  useEffect(() => setMaxPrice(values.max_price || ''), [values.max_price]);

  const commitPrice = () => {
    if ((values.min_price || '') !== minPrice || (values.max_price || '') !== maxPrice) {
      onChange({ min_price: minPrice, max_price: maxPrice });
    }
  };

  const selectCls =
    'w-full rounded-lg border border-border bg-white px-3 py-2.5 text-sm text-navy outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20';

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-1.5">
        <label htmlFor={`${idPrefix}-category`} className="text-sm font-semibold text-navy">
          Category
        </label>
        <select
          id={`${idPrefix}-category`}
          value={values.category_slug || ''}
          onChange={(e) => onChange({ category_slug: e.target.value })}
          className={selectCls}
        >
          <option value="">All categories</option>
          {categories.map((cat) =>
            cat.children?.length ? (
              <optgroup key={cat.id} label={cat.name}>
                <option value={cat.slug}>All {cat.name}</option>
                {cat.children.map((child) => (
                  <option key={child.id} value={child.slug}>
                    {child.name}
                  </option>
                ))}
              </optgroup>
            ) : (
              <option key={cat.id} value={cat.slug}>
                {cat.name}
              </option>
            ),
          )}
        </select>
      </div>

      <fieldset>
        <legend className="mb-2 text-sm font-semibold text-navy">Listing type</legend>
        <div className="flex flex-wrap gap-2">
          {[{ value: '', label: 'All' }, ...LISTING_TYPES].map((t) => {
            const active = (values.listing_type || '') === t.value;
            return (
              <label
                key={t.value || 'all'}
                className={`cursor-pointer rounded-full border px-3 py-1.5 text-xs font-semibold transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primary ${
                  active
                    ? 'border-primary bg-primary text-white'
                    : 'border-border bg-white text-slate-700 hover:border-primary hover:text-primary-hover'
                }`}
              >
                <input
                  type="radio"
                  name={`${idPrefix}-listing-type`}
                  value={t.value}
                  checked={active}
                  onChange={() => onChange({ listing_type: t.value })}
                  className="sr-only"
                />
                {t.label}
              </label>
            );
          })}
        </div>
      </fieldset>

      <div className="flex flex-col gap-1.5">
        <label htmlFor={`${idPrefix}-division`} className="text-sm font-semibold text-navy">
          Division
        </label>
        <select
          id={`${idPrefix}-division`}
          value={values.division_id || ''}
          onChange={(e) => onChange({ division_id: e.target.value })}
          className={selectCls}
        >
          <option value="">All of Bangladesh</option>
          {divisions.map((d) => (
            <option key={d.id} value={d.id}>
              {d.name}
            </option>
          ))}
        </select>
      </div>

      <fieldset>
        <legend className="mb-2 text-sm font-semibold text-navy">Price (৳)</legend>
        <div className="grid grid-cols-2 gap-2">
          <label className="sr-only" htmlFor={`${idPrefix}-min`}>
            Minimum price
          </label>
          <input
            id={`${idPrefix}-min`}
            type="number"
            inputMode="numeric"
            min="0"
            placeholder="Min"
            value={minPrice}
            onChange={(e) => setMinPrice(e.target.value)}
            onBlur={commitPrice}
            onKeyDown={(e) => e.key === 'Enter' && commitPrice()}
            className={selectCls}
          />
          <label className="sr-only" htmlFor={`${idPrefix}-max`}>
            Maximum price
          </label>
          <input
            id={`${idPrefix}-max`}
            type="number"
            inputMode="numeric"
            min="0"
            placeholder="Max"
            value={maxPrice}
            onChange={(e) => setMaxPrice(e.target.value)}
            onBlur={commitPrice}
            onKeyDown={(e) => e.key === 'Enter' && commitPrice()}
            className={selectCls}
          />
        </div>
      </fieldset>
    </div>
  );
}

export default FilterPanel;
