<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use RuntimeException;

class GeneralSettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = GeneralSetting::orderBy('group')->orderBy('id')->get()->map(fn (GeneralSetting $s) => [
            'key'          => $s->key,
            'value'        => $s->type === 'image' ? null : $s->value,
            'image_url'    => $s->type === 'image' ? $s->presentedValue() : null,
            'type'         => $s->type,
            'group'        => $s->group,
            'display_name' => $s->display_name ?: Str::headline($s->key),
            'is_public'    => $s->is_public,
            'updated_at'   => $s->updated_at?->toIso8601String(),
        ]);

        return response()->json([
            'status' => 2000,
            'result' => $settings->groupBy('group'),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'settings'   => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $rows = GeneralSetting::whereIn('key', array_keys($request->input('settings')))
            ->where('type', '!=', 'image')
            ->get()
            ->keyBy('key');

        $errors = [];
        $changed = 0;
        foreach ($request->input('settings') as $key => $value) {
            $row = $rows->get($key);
            if (! $row) {
                continue;
            }
            $value = is_string($value) ? trim($value) : $value;
            if ($value !== null && $value !== '') {
                if ($row->type === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = ["{$row->display_name} must be a valid email address."];
                    continue;
                }
                if ($row->type === 'url' && ! preg_match('#^https?://\S+$#i', $value)) {
                    $errors[$key] = ["{$row->display_name} must start with http:// or https://"];
                    continue;
                }
            }
            $row->value = $value === '' ? null : $value;
            if ($row->isDirty('value')) {
                $row->save();
                $changed++;
            }
        }

        if ($errors) {
            return response()->json([
                'status'  => 3000,
                'message' => 'Some settings were not saved. Check the highlighted fields.',
                'result'  => $errors,
            ]);
        }

        return response()->json([
            'status'  => 2000,
            'message' => $changed ? 'Settings saved.' : 'No changes to save.',
        ]);
    }

    public function uploadImage(Request $request, string $key, ImageStorage $images): JsonResponse
    {
        $setting = GeneralSetting::where('key', $key)->where('type', 'image')->firstOrFail();

        $request->validate([
            'image' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024),
                'dimensions:min_width=800,min_height=300,max_width=8000,max_height=8000',
            ],
        ], [
            'image.dimensions' => 'The image must be at least 800 × 300 pixels.',
        ]);

        try {
            $path = $images->store($request->file('image'), 'settings');
        } catch (RuntimeException $e) {
            report($e);

            return response()->json(['status' => 5000, 'message' => 'The image could not be saved. Please try another file.'], 422);
        }

        $old = $setting->value;
        $setting->value = $path;
        $setting->save();
        $this->deleteStoredFile($old);

        return response()->json([
            'status'  => 2000,
            'message' => "{$setting->display_name} updated.",
            'result'  => ['image_url' => $setting->presentedValue()],
        ]);
    }

    public function removeImage(string $key): JsonResponse
    {
        $setting = GeneralSetting::where('key', $key)->where('type', 'image')->firstOrFail();
        $old = $setting->value;
        $setting->value = null;
        $setting->save();
        $this->deleteStoredFile($old);

        return response()->json(['status' => 2000, 'message' => "{$setting->display_name} removed."]);
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'settings/') && ! str_contains($path, '..')) {
            Storage::disk('public')->delete($path);
        }
    }
}
