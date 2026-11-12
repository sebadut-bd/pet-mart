<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data'   => (object) GeneralSetting::publicMap(),
        ]);
    }
}
