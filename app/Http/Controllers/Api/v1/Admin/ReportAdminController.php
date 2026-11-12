<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\AdminModerationLog;
use App\Models\ProductReport;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportAdminController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status'  => ['nullable', 'in:pending,resolved,reviewed'],
            'keyword' => ['nullable', 'string', 'max:100'],
        ]);

        $page = ProductReport::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('keyword'), fn ($q) => $q->whereHas('product', fn ($p) => $p->withTrashed()->where('title', 'like', $this->like($request->keyword))))
            ->with(['product' => fn ($q) => $q->withTrashed()->with(['primaryImage', 'user:id,name,phone']), 'reporter:id,name,phone'])
            ->latest()
            ->paginate($this->perPage($request));

        $reportTotals = ProductReport::whereIn('product_id', collect($page->items())->pluck('product_id'))
            ->select('product_id', DB::raw('count(*) as total'))->groupBy('product_id')->pluck('total', 'product_id');

        $result = $this->paginated($page, fn (ProductReport $r) => [
            'id'          => $r->id,
            'reason'      => $r->reason,
            'description' => $r->description,
            'status'      => $r->status,
            'created_at'  => $r->created_at?->toIso8601String(),
            'reporter'    => $r->reporter ? ['id' => $r->reporter->id, 'name' => $r->reporter->name, 'phone' => $r->reporter->phone] : null,
            'reports_on_listing' => (int) ($reportTotals[$r->product_id] ?? 1),
            'product'     => $r->product ? [
                'id'      => $r->product->id,
                'title'   => $r->product->title,
                'slug'    => $r->product->slug,
                'status'  => $r->product->trashed() ? 'deleted' : $r->product->status,
                'image'   => ImageStorage::url($r->product->primaryImage?->image_path),
                'seller'  => $r->product->user?->name,
            ] : null,
        ]);
        $result['counts'] = ProductReport::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        return $this->ok($result);
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'take_down' => ['nullable', 'boolean'],
            'reason'    => ['required_if:take_down,1,true', 'nullable', 'string', 'min:5', 'max:500'],
        ]);

        $report = ProductReport::with('product')->findOrFail($id);

        DB::transaction(function () use ($report, $request, $data) {
            $report->update(['status' => 'resolved']);

            if (! empty($data['take_down']) && $report->product && $report->product->status !== 'rejected') {
                $report->product->update(['status' => 'rejected', 'is_featured' => false, 'rejection_reason' => $data['reason']]);
                AdminModerationLog::create([
                    'product_id' => $report->product_id,
                    'admin_id'   => $request->user()->id,
                    'action'     => 'taken_down',
                    'reason'     => $data['reason'],
                ]);
                ProductReport::where('product_id', $report->product_id)->where('status', 'pending')->update(['status' => 'resolved']);
            }
        });

        return $this->ok(['id' => $report->id], ! empty($data['take_down']) ? 'Report resolved and listing taken down.' : 'Report marked as resolved.');
    }

    public function dismiss(int $id): JsonResponse
    {
        $report = ProductReport::findOrFail($id);
        $report->update(['status' => 'reviewed']);

        return $this->ok(['id' => $report->id], 'Report dismissed.');
    }
}
