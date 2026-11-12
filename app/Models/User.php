<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'phone_verified_at',
        'otp_code',
        'otp_expires_at',
        'password',
        'role_id',
        'type',
        'image',
        'bio',
        'address',
        'location_id',
        'referral_code',
        'is_verified',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'status' => 'boolean',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'user_id');
    }

    public function sentInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'sender_id');
    }

    public function receivedInquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'receiver_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function isAdmin(): bool
    {
        return (int) $this->type === 1;
    }

    public function isSeller(): bool
    {
        return (int) $this->type === 2;
    }

    public function isBuyer(): bool
    {
        return (int) $this->type === 3;
    }
}
