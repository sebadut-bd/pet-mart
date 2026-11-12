<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'phone'   => ['required', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        if (! PhoneNumber::isValid($data['phone'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Enter a valid Bangladesh mobile number, e.g. 01712345678.',
                'errors'  => ['phone' => ['Enter a valid Bangladesh mobile number, e.g. 01712345678.']],
            ], 422);
        }

        ContactMessage::create([
            'user_id'    => auth('sanctum')->id(),
            'name'       => trim($data['name']),
            'phone'      => PhoneNumber::normalize($data['phone']),
            'email'      => $data['email'] ?? null,
            'message'    => trim($data['message']),
            'status'     => 'new',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Thanks, we have received your message. Our support team will get back to you soon.',
        ], 201);
    }
}
