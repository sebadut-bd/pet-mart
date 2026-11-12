<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Location;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocationAdminController extends AdminController
{
    private const LISTING_COLUMN = ['division' => 'division_id', 'district' => 'district_id', 'area' => 'area_id'];

    public function index(): JsonResponse
    {
        $rows = Location::orderBy('sort_order')->orderBy('name')->get();

        $listings = [];
        foreach (self::LISTING_COLUMN as $column) {
            Product::whereNotNull($column)->select($column, DB::raw('count(*) as total'))->groupBy($column)
                ->pluck('total', $column)->each(function ($total, $id) use (&$listings) {
                    $listings[$id] = ($listings[$id] ?? 0) + $total;
                });
        }
        $children = $rows->whereNotNull('parent_id')->countBy('parent_id');

        return $this->ok([
            'data' => $rows->map(fn (Location $l) => [
                'id'             => $l->id,
                'parent_id'      => $l->parent_id,
                'type'           => $l->type,
                'name'           => $l->name,
                'bn_name'        => $l->bn_name,
                'sort_order'     => $l->sort_order,
                'status'         => $l->status,
                'children_count' => (int) ($children[$l->id] ?? 0),
                'listings_count' => (int) ($listings[$l->id] ?? 0),
            ])->values(),
            'counts' => $rows->countBy('type'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $parent = $request->filled('parent_id') ? Location::findOrFail($request->parent_id) : null;
        $type = $parent ? $parent->childType() : 'division';
        if (! $type) {
            return $this->error('Areas cannot have sub-locations.');
        }

        $data = $this->validated($request, $type, $parent?->id);
        $location = Location::create($data + ['parent_id' => $parent?->id, 'type' => $type]);

        return $this->ok(['id' => $location->id], ucfirst($type) . ' added.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $location = Location::findOrFail($id);
        $location->update($this->validated($request, $location->type, $location->parent_id, $location->id));

        return $this->ok(['id' => $location->id], ucfirst($location->type) . ' updated.');
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $location = Location::findOrFail($id);
        $location->update(['status' => ! $location->status]);

        return $this->ok(['id' => $location->id, 'status' => $location->status],
            $location->status ? "{$location->name} is shown on the website." : "{$location->name} is hidden from the website.");
    }

    public function destroy(int $id): JsonResponse
    {
        $location = Location::withCount('children')->findOrFail($id);

        if ($location->children_count) {
            return $this->error("{$location->name} has {$location->children_count} sub-location(s). Remove them first, or hide this location instead.");
        }
        $used = Product::withTrashed()->where(self::LISTING_COLUMN[$location->type], $location->id)->count()
            + User::where('location_id', $location->id)->count();
        if ($used) {
            return $this->error("{$location->name} is used by {$used} listing(s) or member(s). Hide it instead of deleting it.");
        }

        $location->delete();

        return $this->ok(null, "{$location->name} deleted.");
    }

    private function validated(Request $request, string $type, ?int $parentId, ?int $ignoreId = null): array
    {
        $request->merge(['slug' => Str::slug((string) $request->input('name'))]);

        $data = $request->validate([
            'name'       => ['required', 'string', 'min:2', 'max:100'],
            'slug'       => ['required', Rule::unique('locations', 'slug')
                ->where('type', $type)
                ->where(fn ($q) => $parentId ? $q->where('parent_id', $parentId) : $q->whereNull('parent_id'))
                ->ignore($ignoreId)],
            'bn_name'    => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status'     => ['nullable', 'boolean'],
        ], ['slug.unique' => 'A location with this name already exists here.']);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['status'] = $data['status'] ?? true;

        return $data;
    }
}
