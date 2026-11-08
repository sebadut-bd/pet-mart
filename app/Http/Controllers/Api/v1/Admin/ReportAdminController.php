<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReport;
use Illuminate\Http\JsonResponse;

class ReportAdminController extends Controller
{
    public function index(): JsonResponse
    {
        $reports = ProductReport::with(['product.primaryImage', 'reporter'])->latest()->get();

        return response()->json([
            'status' => 2000,
            'result' => [
                'data' => $reports,
            ],
            'data'   => $reports,
        ]);
    }

    public function resolve(int $id): JsonResponse
    {
        $report = ProductReport::findOrFail($id);
        $report->update(['status' => 'resolved']);

        return response()->json([
            'status'  => 2000,
            'message' => 'Report resolved.',
            'result'  => $report,
        ]);
    }

    public function dismiss(int $id): JsonResponse
    {
        $report = ProductReport::findOrFail($id);
        $report->update(['status' => 'reviewed']);

        return response()->json([
            'status'  => 2000,
            'message' => 'Report dismissed.',
            'result'  => $report,
        ]);
    }
}
