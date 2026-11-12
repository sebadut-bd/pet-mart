<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Location extends Model
{
    public const TYPES = ['division', 'district', 'area'];

    public const CACHE_KEY = 'locations.public';

    protected $fillable = ['parent_id', 'type', 'name', 'bn_name', 'slug', 'sort_order', 'status'];

    protected $casts = [
        'status'     => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /** Type a child of this location gets, or null for areas. */
    public function childType(): ?string
    {
        $i = array_search($this->type, self::TYPES, true);

        return self::TYPES[$i + 1] ?? null;
    }

    /** Active locations as compact lists for the customer site, cached until a location changes. */
    public static function publicTree(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $rows = static::active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'parent_id', 'type', 'name', 'bn_name']);

            $visibleIds = [];
            $visible = collect();
            foreach (self::TYPES as $type) {
                foreach ($rows->where('type', $type) as $l) {
                    if ($l->parent_id === null || isset($visibleIds[$l->parent_id])) {
                        $visibleIds[$l->id] = true;
                        $visible->push($l);
                    }
                }
            }

            $pick = fn (string $type) => $visible->where('type', $type)->map(fn ($l) => [
                'id'        => $l->id,
                'parent_id' => $l->parent_id,
                'name'      => $l->name,
                'bn_name'   => $l->bn_name,
            ])->values()->all();

            return [
                'divisions' => $pick('division'),
                'districts' => $pick('district'),
                'areas'     => $pick('area'),
            ];
        });
    }
}
