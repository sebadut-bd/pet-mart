<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base for the admin panel API (routes/api.php, prefix v1/admin, middleware web + admin).
 *
 * The Vue admin mixins (resources/js/mixins/http_mixin.js) expect:
 *   { status: 2000, message?: string, result: mixed }
 * and, for lists, result = { data: [], current_page, last_page, per_page, total }.
 * The public customer API uses { status: true, data } instead.
 */
abstract class AdminController extends Controller
{
    protected const MAX_PER_PAGE = 100;

    protected function ok(mixed $result = null, ?string $message = null, int $http = 200): JsonResponse
    {
        return response()->json(array_filter([
            'status'  => 2000,
            'message' => $message,
            'result'  => $result,
        ], fn ($v) => $v !== null), $http);
    }

    protected function error(string $message, int $http = 422): JsonResponse
    {
        return response()->json(['status' => 5000, 'type' => 'error', 'message' => $message], $http);
    }

    protected function perPage(Request $request, int $default = 15): int
    {
        return max(1, min((int) $request->input('perPage', $request->input('per_page', $default)), self::MAX_PER_PAGE));
    }

    protected function paginated(LengthAwarePaginator $page, callable $map): array
    {
        return [
            'data'         => collect($page->items())->map($map)->values(),
            'current_page' => $page->currentPage(),
            'last_page'    => $page->lastPage(),
            'per_page'     => $page->perPage(),
            'total'        => $page->total(),
            'from'         => $page->firstItem(),
        ];
    }

    protected function like(string $term): string
    {
        return '%' . addcslashes(trim($term), '%_\\') . '%';
    }
}
