<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge(['phone' => PhoneNumber::normalize($this->input('phone'))]);
        }
    }

    public function rules(): array
    {
        return [
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'max:100'],
        ];
    }
}
