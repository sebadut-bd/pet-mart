<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class UploadController extends Controller
{
    /**
     * Upload one listing photo from the user's device.
     *
     * Returns a storage-relative `path` (send this in products.images[])
     * and a public `url` for previewing.
     * SVG is not accepted (it can carry scripts). The stored file name is random;
     * the client's original name is never used.
     */
    public function image(Request $request): JsonResponse
    {
        $request->validate([
            'image' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024),
                'dimensions:min_width=200,min_height=200,max_width=8000,max_height=8000',
            ],
        ]);

        $file = $request->file('image');
        $extension = match ($file->getMimeType()) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $dir = 'products/' . now()->format('Y/m');
        $name = Str::uuid()->toString() . '.' . $extension;
        $path = $file->storeAs($dir, $name, 'public');

        if (!$path) {
            return response()->json([
                'status'  => false,
                'message' => 'The photo could not be saved. Please try again.',
            ], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Photo uploaded.',
            'data'    => [
                'path' => $path,
                'url'  => asset('storage/' . $path),
            ],
        ], 201);
    }
}
