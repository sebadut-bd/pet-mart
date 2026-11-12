<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

/**
 * Admin panel: General Settings.
 * Routes are behind the session-based `admin` middleware (see routes/api.php).
 * Admins can only change values of existing keys; new keys are added via GeneralSettingSeeder.
 */
class GeneralSettingController extends Controller
{
    /** All settings grouped for the admin form. */
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
        ]);

        return response()->json([
            'status' => 2000,
            'result' => $settings->groupBy('group'),
        ]);
    }

    /** Update text-type values: { settings: { key: value, ... } } */
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
        foreach ($request->input('settings') as $key => $value) {
            $row = $rows->get($key);
            if (!$row) {
                continue; // unknown or image keys are ignored
            }
            $value = is_string($value) ? trim($value) : $value;
            if ($value !== null && $value !== '') {
                if ($row->type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = ["{$row->display_name} must be a valid email address."];
                    continue;
                }
                if ($row->type === 'url' && !preg_match('#^https?://#i', $value)) {
                    $errors[$key] = ["{$row->display_name} must start with http:// or https://"];
                    continue;
                }
            }
            $row->value = $value === '' ? null : $value;
            $row->save();
        }

        if ($errors) {
            return response()->json([
                'status'  => 3000,
                'message' => 'Some settings were not saved. Please check the highlighted fields.',
                'result'  => $errors,
            ]);
        }

        return response()->json(['status' => 2000, 'message' => 'Settings saved.']);
    }

    /** Upload / replace an image setting (e.g. home_hero_banner). */
    public function uploadImage(Request $request, string $key): JsonResponse
    {
        $setting = GeneralSetting::where('key', $key)->where('type', 'image')->firstOrFail();

        $request->validate([
            'image' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024),
                'dimensions:min_width=800,min_height=300,max_width=8000,max_height=8000',
            ],
        ], [
            'image.dimensions' => 'The image must be at least 800×300 pixels.',
        ]);

        $file = $request->file('image');
        $extension = match ($file->getMimeType()) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };
        $path = $file->storeAs('settings', Str::uuid()->toString() . '.' . $extension, 'public');

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

    /** Remove an image setting (site falls back to its default design). */
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
        // Only delete files this controller created
        if ($path && str_starts_with($path, 'settings/') && !str_contains($path, '..')) {
            Storage::disk('public')->delete($path);
        }
    }
}
