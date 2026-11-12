<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public const MAX_IMAGES = 6;

    private ?Category $category = null;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title'       => is_string($this->title) ? trim($this->title) : $this->title,
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
        ]);
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'min:5', 'max:150'],
            'category_id'   => ['required', 'integer', Rule::exists('categories', 'id')->where('status', true)->whereNull('deleted_at')],
            'description'   => ['required', 'string', 'min:20', 'max:5000'],
            'price'         => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'is_negotiable' => ['nullable', 'boolean'],
            'is_free'       => ['nullable', 'boolean'],
            'listing_type'  => ['required', 'in:sell,adopt,lost,found,stud_service'],
            'condition'     => ['nullable', 'in:new,used'],
            'division_id'   => ['required', 'integer', Rule::exists('locations', 'id')->where('type', 'division')->where('status', true)],
            'district_id'   => ['nullable', 'integer', Rule::exists('locations', 'id')->where('type', 'district')->where('status', true)],
            'area_id'       => ['nullable', 'integer', Rule::exists('locations', 'id')->where('type', 'area')->where('status', true)],
            'full_address'  => ['nullable', 'string', 'max:255'],
            'images'        => ['required', 'array', 'min:1', 'max:' . self::MAX_IMAGES],
            'images.*'      => ['required', 'string', 'max:500', 'distinct', 'regex:#^(products/[\w/.-]+|https://\S+)$#'],
            'attributes'    => ['nullable', 'array', 'max:30'],
            'attributes.*'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required'  => 'At least one photo is required.',
            'images.max'       => 'You can add up to ' . self::MAX_IMAGES . ' photos.',
            'images.*.regex'   => 'Each photo must be uploaded here or be an https:// link.',
            'images.*.distinct'=> 'The same photo was added twice.',
            'division_id.required' => 'Division is required.',
        ];
    }

    public function after(): array
    {
        return [
            fn (Validator $v) => $this->checkLocations($v),
            fn (Validator $v) => $this->checkImages($v),
            fn (Validator $v) => $this->checkAttributes($v),
        ];
    }

    public function category(): ?Category
    {
        if (! $this->category && $this->category_id) {
            $this->category = Category::with(['attributes', 'parent.attributes', 'children'])->find($this->category_id);
        }

        return $this->category;
    }

    public function attributeDefinitions()
    {
        $category = $this->category();
        if (! $category) {
            return collect();
        }

        return $category->attributes->isNotEmpty() ? $category->attributes : ($category->parent?->attributes ?? collect());
    }

    public function attributeValues(): array
    {
        $values = $this->input('attributes');
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(fn ($v) => is_scalar($v) ? trim((string) $v) : '')
            ->filter(fn ($v) => $v !== '')
            ->all();
    }

    private function checkLocations(Validator $v): void
    {
        if ($v->errors()->hasAny(['division_id', 'district_id', 'area_id'])) {
            return;
        }

        if ($this->district_id && ! Location::where('id', $this->district_id)->where('parent_id', $this->division_id)->exists()) {
            $v->errors()->add('district_id', 'The district is not in the selected division.');
        }
        if ($this->area_id) {
            if (! $this->district_id) {
                $v->errors()->add('district_id', 'Choose a district before choosing an area.');
            } elseif (! Location::where('id', $this->area_id)->where('parent_id', $this->district_id)->exists()) {
                $v->errors()->add('area_id', 'The area is not in the selected district.');
            }
        }
    }

    private function checkImages(Validator $v): void
    {
        foreach ((array) $this->input('images', []) as $i => $path) {
            if (is_string($path) && str_starts_with($path, 'products/')
                && (str_contains($path, '..') || ! Storage::disk('public')->exists($path))) {
                $v->errors()->add("images.$i", 'A photo upload was not found. Please upload it again.');
            }
        }
    }

    private function checkAttributes(Validator $v): void
    {
        $category = $this->category();
        if (! $category || $v->errors()->has('category_id')) {
            return;
        }

        $defs = $this->attributeDefinitions()->keyBy('attribute_key');
        $values = $this->attributeValues();

        foreach ($values as $key => $value) {
            $def = $defs->get($key);
            if (! $def) {
                $v->errors()->add("attributes.$key", 'This field does not belong to the selected category.');
                continue;
            }
            $ok = match ($def->type) {
                'number'  => is_numeric($value),
                'boolean' => in_array($value, ['Yes', 'No'], true),
                'select'  => in_array($value, (array) $def->options, true),
                default   => true,
            };
            if (! $ok) {
                $v->errors()->add("attributes.$key", "Choose a valid value for {$def->name}.");
            }
        }

        foreach ($defs as $key => $def) {
            if ($def->is_required && ! array_key_exists($key, $values)) {
                $v->errors()->add("attributes.$key", "{$def->name} is required.");
            }
        }
    }
}
