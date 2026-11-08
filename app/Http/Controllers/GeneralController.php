<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function getGeneralData(Request $request): JsonResponse
    {
        $keys = $request->all();
        $result = [];

        if (in_array('module', $keys)) {
            $result['module'] = Module::where('parent_id', 0)->where('status', true)->get();
        }

        if (in_array('role', $keys)) {
            $result['role'] = Role::where('status', true)->get();
        }

        if (in_array('category', $keys)) {
            $result['category'] = Category::where('parent_id', null)->where('status', true)->get();
        }

        if (in_array('user', $keys)) {
            $result['user'] = User::select('id', 'name', 'email', 'phone')->limit(100)->get();
        }

        return response()->json([
            'status' => 2000,
            'result' => $result,
        ]);
    }
}
