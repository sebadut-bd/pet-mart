<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryAdminLog extends Model
{
    protected $fillable = [
        'admin_id',
        'product_id',
        'buyer_id',
        'inquiry_id',
        'action',
        'old_message',
        'affected',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
