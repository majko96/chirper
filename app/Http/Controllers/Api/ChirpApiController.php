<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ChirpApiController extends Controller
{

    /**
     * @param $id
     * @return JsonResponse
     */
    public function usersLikes($id): JsonResponse
    {
        $users = User::join('likes', 'users.id', '=', 'likes.user_id')
            ->where('likes.chirp_id', $id)
            ->select('users.*')
            ->get();

        return response()->json($users);
    }
}
