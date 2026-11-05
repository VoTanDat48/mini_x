<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkController extends Controller
{
    /**
     * Display a listing of the user's bookmarked tweets with pagination.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $tweets = Tweet::whereHas('bookmarks', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->withInteractionDetails($userId)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Bookmarks', [
            'tweets' => $tweets,
        ]);
    }

    /**
     * Toggle bookmark status for the specified tweet.
     */
    public function toggle(Request $request, Tweet $tweet)
    {
        $userId = $request->user()->id;
        $existing = Bookmark::where('user_id', $userId)
            ->where('tweet_id', $tweet->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $bookmarked = false;
        } else {
            Bookmark::create([
                'user_id' => $userId,
                'tweet_id' => $tweet->id,
            ]);
            $bookmarked = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'bookmarked' => $bookmarked,
                'bookmarks_count' => $tweet->bookmarks()->count(),
            ]);
        }

        return back();
    }
}
