<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use RuntimeException;

class UploadAdminController extends AdminController
{
    public function image(Request $request, ImageStorage $images): JsonResponse
    {
        $request->validate([
            'image'  => ['required', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024), ImageStorage::RULES],
            'folder' => ['required', 'in:banners,categories'],
        ]);

        try {
            $path = $images->store($request->file('image'), $request->folder);
        } catch (RuntimeException $e) {
            report($e);

            return $this->error('The image could not be saved. Please try another file.');
        }

        return $this->ok(['path' => $path, 'url' => ImageStorage::url($path)], 'Image uploaded.', 201);
    }
}
