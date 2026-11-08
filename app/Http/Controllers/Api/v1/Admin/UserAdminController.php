<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::with('location')->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => 2000,
            'result' => [
                'data'         => UserResource::collection($users)->response()->getData(true)['data'],
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
            'data'   => UserResource::collection($users),
        ]);
    }

    public function toggleVerification(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => !$user->is_verified]);

        return response()->json([
            'status'  => 2000,
            'message' => 'Seller verification status updated.',
            'result'  => new UserResource($user),
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['status' => !$user->status]);

        return response()->json([
            'status'  => 2000,
            'message' => 'User account status updated.',
            'result'  => new UserResource($user),
        ]);
    }
}
