<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = ['user_id', 'name', 'phone', 'email', 'message', 'status', 'admin_note', 'ip_address'];

    protected $hidden = ['ip_address'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
