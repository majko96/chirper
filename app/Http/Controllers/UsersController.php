<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(Request $request): View {
        $paramUserId = $request->get('userId');

        if ($paramUserId) {
            $users = User::where('id', $paramUserId)->paginate(6)->withQueryString();
        } else {
            $users = User::orderBy('name', 'asc')->paginate(6)->withQueryString();
        }

        return view('users.users', [
            'users' => $users,
        ]);
    }
}
