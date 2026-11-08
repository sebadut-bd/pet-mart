<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Get hierarchy of divisions, districts, and areas.
     */
    public function index(): JsonResponse
    {
        $divisions = Location::where('type', 'division')
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        $districts = Location::where('type', 'district')
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        $areas = Location::where('type', 'area')
            ->where('status', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'divisions' => $divisions,
                'districts' => $districts,
                'areas'     => $areas,
            ],
        ]);
    }
}
