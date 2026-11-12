<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'description' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_negotiable' => ['nullable', 'boolean'],
            'is_free' => ['nullable', 'boolean'],
            'listing_type' => ['required', 'in:sell,adopt,lost,found,stud_service'],
            'condition' => ['nullable', 'in:new,used'],
            'division_id' => ['nullable', 'exists:locations,id'],
            'district_id' => ['nullable', 'exists:locations,id'],
            'area_id' => ['nullable', 'exists:locations,id'],
            'full_address' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'min:1'],
            'images.*' => ['nullable', 'string', 'max:255'], // uploaded storage path (POST /uploads/images) or https URL
            'attributes' => ['nullable', 'array'], // key-value map of dynamic fields e.g. ["breed" => "Persian", "age" => "2 months"]
        ];
    }
}
