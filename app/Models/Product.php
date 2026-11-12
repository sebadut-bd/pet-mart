<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'is_negotiable',
        'is_free',
        'listing_type',
        'condition',
        'division_id',
        'district_id',
        'area_id',
        'full_address',
        'is_featured',
        'is_verified',
        'is_urgent',
        'status',
        'rejection_reason',
        'views',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_negotiable' => 'boolean',
        'is_free'       => 'boolean',
        'is_featured'   => 'boolean',
        'is_verified'   => 'boolean',
        'is_urgent'     => 'boolean',
        'views'         => 'integer',
        'expires_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class, 'product_id')->where('is_primary', true);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class, 'product_id');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'product_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProductReport::class, 'product_id');
    }

    public function moderationLogs(): HasMany
    {
        return $this->hasMany(AdminModerationLog::class, 'product_id')->latest();
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'division_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'district_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'area_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
