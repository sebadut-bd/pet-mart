<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use RuntimeException;

class UploadController extends Controller
{
    private const FOLDERS = [
        'listing' => 'products',
        'avatar'  => 'avatars',
    ];

    public function image(Request $request, ImageStorage $images): JsonResponse
    {
        $request->validate([
            'image'   => ['required', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024), ImageStorage::RULES],
            'purpose' => ['nullable', 'in:' . implode(',', array_keys(self::FOLDERS))],
        ]);

        try {
            $path = $images->store($request->file('image'), self::FOLDERS[$request->input('purpose', 'listing')]);
        } catch (RuntimeException $e) {
            report($e);

            return response()->json(['status' => false, 'message' => 'The photo could not be saved. Please try another photo.'], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Photo uploaded.',
            'data'    => ['path' => $path, 'url' => ImageStorage::url($path)],
        ], 201);
    }
}
