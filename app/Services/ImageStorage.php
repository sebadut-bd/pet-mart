<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores uploaded photos on the public disk.
 *
 * Every image is decoded and re-encoded with GD. This removes EXIF and other
 * metadata (phone photos often carry the GPS location of the seller's home)
 * and anything appended to the file. EXIF orientation is applied first so
 * photos are not saved sideways.
 */
class ImageStorage
{
    private const MAX_DIMENSION = 2000;

    public const RULES = 'dimensions:min_width=200,min_height=200,max_width=8000,max_height=8000';

    public function store(UploadedFile $file, string $folder): string
    {
        $mime = $file->getMimeType();
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $source) {
            throw new RuntimeException('The image could not be read.');
        }

        if ($mime === 'image/jpeg') {
            $source = $this->applyOrientation($source, $file->getRealPath());
        }
        $image = $this->scaleDown($source);

        [$extension, $binary] = $this->encode($image, $mime);
        imagedestroy($image);

        $path = trim($folder, '/') . '/' . now()->format('Y/m') . '/' . Str::uuid() . '.' . $extension;
        if (! Storage::disk('public')->put($path, $binary)) {
            throw new RuntimeException('The image could not be saved.');
        }

        return $path;
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return preg_match('#^https?://#i', $path) ? $path : asset('storage/' . ltrim($path, '/'));
    }

    private function applyOrientation(\GdImage $image, string $realPath): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }
        $exif = @exif_read_data($realPath);
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };
        if ($angle === 0) {
            return $image;
        }
        $rotated = imagerotate($image, $angle, 0);
        imagedestroy($image);

        return $rotated ?: $image;
    }

    private function scaleDown(\GdImage $image): \GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $scale = min(1, self::MAX_DIMENSION / max($w, $h));
        if ($scale >= 1) {
            return $image;
        }
        $scaled = imagescale($image, (int) round($w * $scale), (int) round($h * $scale));
        imagedestroy($image);

        return $scaled;
    }

    private function encode(\GdImage $image, ?string $mime): array
    {
        ob_start();
        if ($mime === 'image/png') {
            imagesavealpha($image, true);
            imagepng($image, null, 6);
            $ext = 'png';
        } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
            imagewebp($image, null, 85);
            $ext = 'webp';
        } else {
            imagejpeg($image, null, 85);
            $ext = 'jpg';
        }

        return [$ext, (string) ob_get_clean()];
    }
}
