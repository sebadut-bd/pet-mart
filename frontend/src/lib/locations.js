/**
 * Helpers for the division › district › area tree from GET /locations.
 * locations = { divisions: [], districts: [], areas: [] }, each item { id, parent_id, name, bn_name }.
 */
export function indexLocations(locations = {}) {
  const divisions = locations.divisions || [];
  const districts = locations.districts || [];
  const areas = locations.areas || [];
  const byId = new Map();
  [...divisions, ...districts, ...areas].forEach((l) => byId.set(String(l.id), l));

  const childrenOf = (list, parentId) =>
    parentId ? list.filter((l) => String(l.parent_id) === String(parentId)) : [];

  const path = (id) => {
    const out = [];
    let node = byId.get(String(id));
    while (node) {
      out.unshift(node);
      node = node.parent_id ? byId.get(String(node.parent_id)) : null;
    }
    return out;
  };

  const searchable = [
    ...areas.map((l) => ({ ...l, level: 'area' })),
    ...districts.map((l) => ({ ...l, level: 'district' })),
    ...divisions.map((l) => ({ ...l, level: 'division' })),
  ].map((l) => {
    const chain = path(l.id);
    return {
      ...l,
      chain,
      context: chain.slice(0, -1).reverse().map((c) => (c === chain[0] ? `${c.name} division` : c.name)).join(' · '),
      haystack: `${l.name} ${l.bn_name || ''}`.toLowerCase(),
    };
  });

  return {
    divisions,
    districtsOf: (divisionId) => childrenOf(districts, divisionId),
    areasOf: (districtId) => childrenOf(areas, districtId),
    get: (id) => (id ? byId.get(String(id)) : undefined),
    path,
    search(query, limit = 8) {
      const q = query.trim().toLowerCase();
      if (q.length < 2) return [];
      const starts = [];
      const contains = [];
      searchable.forEach((l) => {
        if (l.haystack.startsWith(q) || l.haystack.includes(` ${q}`)) starts.push(l);
        else if (l.haystack.includes(q)) contains.push(l);
      });
      return [...starts, ...contains].slice(0, limit);
    },
    /** { division_id, district_id, area_id } for any location id. */
    selectionFor(id) {
      const sel = { division_id: '', district_id: '', area_id: '' };
      const chain = path(id);
      ['division_id', 'district_id', 'area_id'].forEach((key, i) => {
        if (chain[i]) sel[key] = String(chain[i].id);
      });
      return sel;
    },
  };
}

export const locationOptionLabel = (l) => (l.bn_name ? `${l.name} (${l.bn_name})` : l.name);
