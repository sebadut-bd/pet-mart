<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'referral_code' => ['nullable', 'string', 'max:30'],
        ];
    }
}
