<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Public search & filter listings.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::where('status', 'active')
            ->with(['category', 'user', 'primaryImage', 'images', 'division', 'district', 'area']);

        // Search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter (by ID or Slug)
        if ($request->filled('category_slug')) {
            $cat = Category::where('slug', $request->category_slug)->first();
            if ($cat) {
                $catIds = Category::where('id', $cat->id)->orWhere('parent_id', $cat->id)->pluck('id');
                $query->whereIn('category_id', $catIds);
            }
        } elseif ($request->filled('category_id')) {
            $catIds = Category::where('id', $request->category_id)->orWhere('parent_id', $request->category_id)->pluck('id');
            $query->whereIn('category_id', $catIds);
        }

        // Location filters
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Listing type filter (sell, adopt, lost, found, stud_service)
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Dynamic key-value attribute filter (e.g. attributes[breed]=Persian)
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $key => $val) {
                if (!empty($val)) {
                    $query->whereHas('attributes', function ($q) use ($key, $val) {
                        $q->where('attribute_key', $key)->where('attribute_value', $val);
                    });
                }
            }
        }

        // Sorting
        $sortBy = $request->get('sort', 'latest');
        match ($sortBy) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderBy('views', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $products = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'status' => true,
            'data'   => ProductResource::collection($products),
            'meta'   => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    /**
     * Get detail of single listing by ID or Slug.
     */
    public function show(string $identifier): JsonResponse
    {
        $product = Product::where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->with(['category', 'subCategory', 'user', 'images', 'attributes', 'division', 'district', 'area'])
            ->firstOrFail();

        // Increment view count
        $product->increment('views');

        return response()->json([
            'status' => true,
            'data'   => new ProductDetailResource($product),
        ]);
    }

    /**
     * Store new seller listing (submits for admin moderation).
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $user = $request->user();
        $slug = Str::slug($request->title) . '-' . Str::random(5);

        $product = Product::create([
            'user_id'         => $user->id,
            'category_id'     => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'title'           => $request->title,
            'slug'            => $slug,
            'description'     => $request->description,
            'price'           => $request->price,
            'is_negotiable'   => $request->boolean('is_negotiable', false),
            'is_free'         => $request->boolean('is_free', false),
            'listing_type'    => $request->listing_type ?? 'sell',
            'condition'       => $request->condition,
            'division_id'     => $request->division_id,
            'district_id'     => $request->district_id,
            'area_id'         => $request->area_id,
            'full_address'    => $request->full_address,
            'status'          => 'pending', // Awaiting admin approval
        ]);

        // Attach photos
        if ($request->has('images') && is_array($request->images)) {
            foreach ($request->images as $index => $imgPath) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imgPath,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        // Attach dynamic attributes (breed, age, vaccination, etc.)
        if ($request->has('attributes') && is_array($request->attributes)) {
            foreach ($request->attributes as $key => $val) {
                if ($val !== null && $val !== '') {
                    ProductAttribute::create([
                        'product_id'      => $product->id,
                        'attribute_key'   => $key,
                        'attribute_value' => is_array($val) ? implode(', ', $val) : (string) $val,
                    ]);
                }
            }
        }

        return response()->json([
            'status'  => true,
            'message' => 'Listing submitted successfully! It will be reviewed by admin before appearing live.',
            'data'    => new ProductDetailResource($product->load(['category', 'user', 'images', 'attributes'])),
        ], 201);
    }

    /**
     * Seller's own listings in dashboard.
     */
    public function myListings(Request $request): JsonResponse
    {
        $user = $request->user();
        $products = Product::where('user_id', $user->id)
            ->with(['category', 'primaryImage', 'images'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => ProductResource::collection($products),
        ]);
    }

    /**
     * Mark listing as sold.
     */
    public function markAsSold(Request $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $product->update(['status' => 'sold']);

        return response()->json([
            'status'  => true,
            'message' => 'Listing marked as sold.',
            'data'    => new ProductDetailResource($product),
        ]);
    }

    /**
     * Delete listing.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = Product::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $product->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Listing deleted successfully.',
        ]);
    }
}
