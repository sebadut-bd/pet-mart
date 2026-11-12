<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use App\Services\ImageStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAdminController extends AdminController
{
    private const TREND_DAYS = 14;

    public function stats(): JsonResponse
    {
        $byStatus = Product::select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        $categories = Category::whereNull('parent_id')
            ->withCount(['products as direct_count'])
            ->with(['children' => fn ($q) => $q->withCount('products')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($c) => ['name' => $c->name, 'total' => $c->direct_count + $c->children->sum('products_count')])
            ->sortByDesc('total')
            ->values();

        return $this->ok([
            'cards' => [
                'total_listings'    => (int) $byStatus->sum(),
                'active_listings'   => (int) ($byStatus['active'] ?? 0),
                'pending_approvals' => (int) ($byStatus['pending'] ?? 0),
                'rejected_listings' => (int) ($byStatus['rejected'] ?? 0),
                'sold_listings'     => (int) ($byStatus['sold'] ?? 0),
                'total_users'       => User::count(),
                'new_users_week'    => User::where('created_at', '>=', now()->subDays(7))->count(),
                'verified_sellers'  => User::where('is_verified', true)->count(),
                'total_categories'  => Category::count(),
                'pending_reports'   => ProductReport::where('status', 'pending')->count(),
                'new_messages'      => ContactMessage::where('status', 'new')->count(),
                'inquiries_week'    => Inquiry::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'status_chart'   => [
                'labels' => ['Live', 'Pending', 'Sold', 'Rejected'],
                'series' => [
                    (int) ($byStatus['active'] ?? 0),
                    (int) ($byStatus['pending'] ?? 0),
                    (int) ($byStatus['sold'] ?? 0),
                    (int) ($byStatus['rejected'] ?? 0),
                ],
            ],
            'category_chart' => [
                'labels' => $categories->pluck('name'),
                'series' => $categories->pluck('total'),
            ],
            'trend_chart'    => $this->trend(),
            'pending_queue'  => Product::where('status', 'pending')->with(['user:id,name', 'category:id,name', 'primaryImage'])
                ->oldest()->limit(5)->get()->map(fn (Product $p) => $this->brief($p)),
            'recent_listings' => Product::with(['user:id,name', 'category:id,name', 'primaryImage'])
                ->latest()->limit(6)->get()->map(fn (Product $p) => $this->brief($p)),
        ]);
    }

    private function trend(): array
    {
        $from = now()->subDays(self::TREND_DAYS - 1)->startOfDay();

        $listings = Product::withTrashed()->where('created_at', '>=', $from)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as total'))->groupBy('day')->pluck('total', 'day');
        $users = User::where('created_at', '>=', $from)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as total'))->groupBy('day')->pluck('total', 'day');

        $days = collect(range(0, self::TREND_DAYS - 1))->map(fn ($i) => $from->copy()->addDays($i)->toDateString());

        return [
            'labels'   => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->values(),
            'listings' => $days->map(fn ($d) => (int) ($listings[$d] ?? 0))->values(),
            'users'    => $days->map(fn ($d) => (int) ($users[$d] ?? 0))->values(),
        ];
    }

    private function brief(Product $p): array
    {
        return [
            'id'         => $p->id,
            'title'      => $p->title,
            'slug'       => $p->slug,
            'status'     => $p->status,
            'price'      => $p->price !== null ? (float) $p->price : null,
            'is_free'    => (bool) $p->is_free,
            'image'      => ImageStorage::url($p->primaryImage?->image_path),
            'category'   => $p->category?->name,
            'seller'     => $p->user?->name,
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }
}
