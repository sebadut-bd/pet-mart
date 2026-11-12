<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GeneralSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'display_name', 'is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public const PUBLIC_CACHE_KEY = 'general_settings.public';

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::PUBLIC_CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public function presentedValue(): mixed
    {
        if ($this->type === 'image' && $this->value) {
            return str_starts_with($this->value, 'http') ? $this->value : asset('storage/' . $this->value);
        }
        if ($this->type === 'boolean') {
            return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
        }

        return $this->value;
    }

    public static function publicMap(): array
    {
        return Cache::rememberForever(self::PUBLIC_CACHE_KEY, function () {
            return static::where('is_public', true)
                ->get()
                ->mapWithKeys(fn (self $s) => [$s->key => $s->presentedValue()])
                ->all();
        });
    }
}
