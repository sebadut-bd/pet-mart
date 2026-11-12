<?php

namespace App\Http\Requests;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        if ($this->filled('name')) {
            $this->merge(['name' => trim((string) $this->input('name'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'min:2', 'max:100'],
            'phone'         => ['required', 'string', 'regex:' . PhoneNumber::PATTERN],
            'password'      => ['required', 'string', 'min:6', 'max:100'],
            'referral_code' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid Bangladesh mobile number, e.g. 01712345678.',
        ];
    }
}
