<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardAdminController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalListings    = Product::count();
        $activeListings   = Product::where('status', 'active')->count();
        $pendingApprovals = Product::where('status', 'pending')->count();
        $rejectedListings = Product::where('status', 'rejected')->count();
        $soldListings     = Product::where('status', 'sold')->count();
        $totalUsers       = User::count();
        $verifiedSellers  = User::where('type', 2)->where('is_verified', true)->count();
        $totalCategories  = Category::count();
        $pendingReports   = ProductReport::where('status', 'pending')->count();

        // Category distribution
        $categories = Category::whereNull('parent_id')->withCount('products')->get();
        $categoryChart = [
            'series' => $categories->pluck('products_count')->toArray(),
            'labels' => $categories->pluck('name')->toArray(),
        ];

        // Listing Status Chart
        $statusChart = [
            'series' => [
                [
                    'name' => 'Count',
                    'data' => [$activeListings, $pendingApprovals, $soldListings, $rejectedListings]
                ]
            ],
            'categories' => ['Active', 'Pending', 'Sold', 'Rejected']
        ];

        $recentProducts = Product::with(['user', 'category'])->latest()->limit(6)->get();

        $responseData = [
            'total_listings'    => $totalListings,
            'active_listings'   => $activeListings,
            'pending_approvals' => $pendingApprovals,
            'rejected_listings' => $rejectedListings,
            'sold_listings'     => $soldListings,
            'total_users'       => $totalUsers,
            'verified_sellers'  => $verifiedSellers,
            'total_categories'  => $totalCategories,
            'pending_reports'   => $pendingReports,
            'category_chart'    => $categoryChart,
            'status_chart'      => $statusChart,
            'recent_products'   => $recentProducts,
        ];

        return response()->json([
            'status' => 2000,
            'result' => $responseData,
            'data'   => $responseData,
        ]);
    }
}
