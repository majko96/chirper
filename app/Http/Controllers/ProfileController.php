<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Chirp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $oldImage = $request->user()->image;

        if ($request->image !== null && !is_string($request->image)) {
            $fileName = time() . '-' . $request->image->getClientOriginalName();
            $request->image->storeAs('public/profilePhotos', $fileName);
            $request->user()->image = $fileName;
            if ($oldImage !== null && $oldImage !== $request->image) {
                $imagePath = public_path('storage/profilePhotos/' . $oldImage);
                if(File::exists($imagePath)){
                    unlink($imagePath);
                }
            }
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            abort(404);
        }

        $chirps = Chirp::with('user')
            ->where('user_id', $id)
            ->where(function ($query) {
                $query->where('visible', false);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(6)->withQueryString();

        return view('profile.show', [
            'user' => $user,
            'chirps' => $chirps,
        ]);
    }
}
