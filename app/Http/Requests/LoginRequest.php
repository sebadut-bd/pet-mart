<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $phone = preg_replace('/[^\d+]/', '', $this->phone);
            if (!str_starts_with($phone, '+880')) {
                if (str_starts_with($phone, '880')) {
                    $phone = '+' . $phone;
                } elseif (str_starts_with($phone, '0')) {
                    $phone = '+88' . $phone;
                } else {
                    $phone = '+880' . $phone;
                }
            }
            $this->merge(['phone' => $phone]);
        }
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
