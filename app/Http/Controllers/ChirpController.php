<?php

namespace App\Http\Controllers;

use App\Events\NewCommentAdded;
use App\Models\Chirp;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use App\Notifications\NewCommentNotification;
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
    public function detail($id)
    {
        auth()->user()->unreadNotifications
            ->where('type', NewCommentNotification::class)
            ->where('data.post_id', $id)
            ->markAsRead();

        $chirp = Chirp::with(['user', 'comments.user'])
            ->where('id', $id)
            ->first();

        return view('chirps.detail', [
            'chirp' => $chirp,
        ]);
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'user_id'   => 'required|exists:users,id',
            'content'   => 'required|string',
        ]);

        $comment = new Comment([
            'user_id'   => $request->input('user_id'),
            'chirp_id'  => $id,
            'content'   => $request->input('content'),
        ]);

        $comment->save();
        $chirp = Chirp::find($id);
        event(new NewCommentAdded($chirp, Auth::user()));
//        return response()->json(['message' => 'Comment added successfully']);
        return redirect(route('chirp.detail', ['id' => $id]));
    }

    public function removeComment(Request $request, $id, $commentId) {

        $comment = Comment::find($commentId);
        $comment->delete();
        return redirect(route('chirp.detail', ['id' => $id]));
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

        if (!is_string($request->image)) {
            if ($request->image !== null && !is_string($request->image)) {
                $fileName = time() . '-' . $request->image->getClientOriginalName();
                $request->image->storeAs('public/images', $fileName);
                $validated['image'] = $fileName;
            } else {
                $validated['image'] = null;
            }
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

    public function likeChirp(Request $request, $id)
    {
        $chirp = Chirp::findOrFail($id);
        $user = Auth::user();

        if (!$user->likes()->where('chirp_id', $chirp->id)->exists()) {
            $like = $user->likes()->create(['chirp_id' => $chirp->id]);
            $likeCount = $chirp->likesCount(); // Get updated like count
            return response()->json(['message' => 'Chirp liked successfully', 'like_count' => $likeCount]);
        }

        return response()->json(['message' => 'User has already liked this chirp']);
    }

    public function unlikeChirp(Request $request, $id)
    {
        $chirp = Chirp::findOrFail($id);
        $user = Auth::user();

        $like = $user->likes()->where('chirp_id', $chirp->id)->first();

        if ($like) {
            $like->delete();
            $likeCount = $chirp->likesCount(); // Get updated like count
            return response()->json(['message' => 'Chirp unliked successfully', 'like_count' => $likeCount]);
        }

        return response()->json(['message' => 'User has not liked this chirp']);
    }

    public function likeComment($commentId)
    {
        $user = auth()->user();

        // Check if the user has already liked the comment
        if (!$user->likes()->where('comment_id', $commentId)->exists()) {
            // Create a new like for comment
            Like::create([
                'user_id' => $user->id,
                'comment_id' => $commentId,
            ]);

            return response()->json(['message' => 'Comment liked successfully']);
        }

        return response()->json(['message' => 'User has already liked this comment']);
    }

    public function unlikeComment($commentId)
    {
        $user = auth()->user();

        // Find and delete the like for comment
        $like = $user->likes()->where('comment_id', $commentId)->first();

        if ($like) {
            $like->delete();
            return response()->json(['message' => 'Comment unliked successfully']);
        }

        return response()->json(['message' => 'User has not liked this comment']);
    }
}
