<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $searchOwnItems = false;
        $paramUserId = $request->get('userId');
        if ($userId === (int)$paramUserId) {
            $searchOwnItems = true;
        }

        $sortBy = 'created_at';
        $order = 'desc';

        $sortByParam = $request->get('sortBy');
        if (!empty($sortByParam)) {
            $sortBy = $request->get('sortBy');
        }
        $orderParam = $request->get('order');
        if (!empty($orderParam)) {
            $order = $request->get('order');
        }

        if (!empty($paramUserId) && !$searchOwnItems) {
            $chirps = Chirp::with('user')
                ->where('user_id', $paramUserId)
                ->where('visible', false)
                ->orderBy($sortBy, $order)
                ->paginate(6)->withQueryString();
        } elseif ($searchOwnItems) {
            $chirps = Chirp::with('user')
                ->where('user_id', $paramUserId)
                ->orderBy($sortBy, $order)
                ->paginate(6)->withQueryString();
        } else {
            $chirps = Chirp::with('user')
                ->where('user_id', $userId)
                ->orWhere(function ($query) {
                    $query->where('visible', false);
                })
                ->orderBy($sortBy, $order)
                ->paginate(6)->withQueryString();
        }

        return view('chirps.index', [
            'chirps' => $chirps,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'visible' => [
                'boolean',
            ],
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->image) {
            $fileName = time() . '-' . $request->image->getClientOriginalName();
            $request->image->storeAs('public/images', $fileName);
            $validated['image'] = $fileName;
        }

        $request->user()->chirps()->create($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * @throws AuthorizationException
     */
    public function edit(Chirp $chirp): View
    {
        $this->authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @throws AuthorizationException
     */
    public function update(Request $request, Chirp $chirp): RedirectResponse
    {
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'visible' => [
                'boolean',
            ],
//            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->image !== null && !is_string($request->image)) {
            $fileName = time() . '-' . $request->image->getClientOriginalName();
            $request->image->storeAs('public/images', $fileName);
            $validated['image'] = $fileName;
        } elseif (is_string($request->image)) {
            $validated['image'] = $request->image;
        } else {
            $validated['image'] = null;
        }

        $chirp->update($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Remove the specified resource from storage.
     * @throws AuthorizationException
     */
    public function destroy(Chirp $chirp): RedirectResponse
    {
        $this->authorize('delete', $chirp);

        $chirp->delete();

        return redirect(route('chirps.index'));
    }
}
