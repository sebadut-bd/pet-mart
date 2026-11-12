<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()
            ->json(['status' => true, 'data' => Location::publicTree()])
            ->header('Cache-Control', 'public, max-age=300');
    }
}
