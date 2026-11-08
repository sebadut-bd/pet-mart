<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status'     => 'boolean',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
    ];
}
