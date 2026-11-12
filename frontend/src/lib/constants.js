export const APP_NAME = 'PetMart';
export const APP_TAGLINE = 'Bangladesh Pet Marketplace';

// Values must match the backend enum on products.listing_type
export const LISTING_TYPES = [
  { value: 'sell', label: 'For Sale', color: 'primary' },
  { value: 'adopt', label: 'Adoption', color: 'blue' },
  { value: 'stud_service', label: 'Stud Service', color: 'purple' },
  { value: 'lost', label: 'Lost Pet', color: 'red' },
  { value: 'found', label: 'Found Pet', color: 'amber' },
];

export const LISTING_TYPE_MAP = Object.fromEntries(LISTING_TYPES.map((t) => [t.value, t]));

// Values must match ProductController@index `sort` handling
export const SORT_OPTIONS = [
  { value: 'latest', label: 'Newest first' },
  { value: 'price_low', label: 'Price: low to high' },
  { value: 'price_high', label: 'Price: high to low' },
  { value: 'popular', label: 'Most viewed' },
];

export const CONDITIONS = [
  { value: 'new', label: 'New' },
  { value: 'used', label: 'Used' },
];

export const REPORT_REASONS = [
  'Spam or scam',
  'Fake photos or misleading breed',
  'Animal welfare concern',
  'Prohibited item or breed',
  'Already sold or unavailable',
  'Other',
];

export const STATUS_BADGES = {
  pending: { label: 'Pending review', color: 'amber' },
  active: { label: 'Live', color: 'primary' },
  rejected: { label: 'Rejected', color: 'red' },
  sold: { label: 'Sold', color: 'secondary' },
  expired: { label: 'Expired', color: 'secondary' },
};

export const MAX_PHOTOS = 6;
export const PER_PAGE = 12;

// Bangladesh mobile number: 11 digits starting with 01 and an operator digit 3-9
export const BD_PHONE_REGEX = /^01[3-9]\d{8}$/;
export const MIN_PASSWORD_LENGTH = 6;
