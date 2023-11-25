<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UserController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $data = User::query()
            ->where('name', 'LIKE', '%' . $request->get('query') . '%')
            ->orWhere('email', 'LIKE', '%' . $request->get('query') . '%')
            ->get();

        return response()->json($data);
    }
}
