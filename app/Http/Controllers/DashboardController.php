<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\ProductReport;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_users'       => User::count(),
            'verified_sellers'  => User::where('type', 2)->where('is_verified', true)->count(),
            'total_products'    => Product::where('status', 'active')->count(),
            'pending_products'  => Product::where('status', 'pending')->count(),
            'rejected_products' => Product::where('status', 'rejected')->count(),
            'total_inquiries'   => Inquiry::count(),
            'total_categories'  => Category::count(),
            'pending_reports'   => ProductReport::where('status', 'pending')->count(),
            'recent_products'   => Product::with(['user', 'category', 'primaryImage'])
                                    ->latest()->limit(8)->get(),
        ];

        return view('backend.dashboard', $data);
    }
}
