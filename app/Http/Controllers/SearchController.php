<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /**
     * Search tweets and users.
     */
    public function index(Request $request)
    {
        $query = trim($request->query('q', ''));
        $type = $request->query('type', 'top'); // 'top' | 'latest' | 'people'
        $currentUserId = $request->user()?->id;

        if ($query === '') {
            return redirect()->route('explore');
        }

        if ($type === 'people') {
            $users = User::where('status', 'active')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('username', 'like', "%{$query}%");
                })
                ->withCount(['followers', 'following'])
                ->paginate(15)
                ->withQueryString();

            if ($currentUserId) {
                $users->getCollection()->transform(function ($u) use ($currentUserId) {
                    $u->is_following = $u->followers()->where('follower_id', $currentUserId)->exists();
                    return $u;
                });
            }

            return Inertia::render('Search', [
                'query'  => $query,
                'type'   => $type,
                'users'  => $users,
                'tweets' => null,
            ]);
        }

        // Tweet search: 'latest' or 'top'
        $cleanTag = ltrim($query, '#');
        $tweetsQuery = Tweet::where(function ($q) use ($query, $cleanTag) {
                $q->where('tweet', 'like', "%{$query}%")
                  ->orWhereHas('hashtags', fn ($hq) => $hq->where('tag', $cleanTag));
            })
            ->withInteractionDetails($currentUserId);

        if ($type === 'latest') {
            $tweetsQuery->orderByDesc('created_at')->orderByDesc('id');
        } else {
            // 'top'
            $tweetsQuery->orderByDesc('likes_count')->orderByDesc('created_at');
        }

        $tweets = $tweetsQuery->paginate(15)->withQueryString();

        return Inertia::render('Search', [
            'query'  => $query,
            'type'   => $type,
            'tweets' => $tweets,
            'users'  => null,
        ]);
    }

    /**
     * Explore trending topics and curated tweets.
     */
    public function explore(Request $request): Response
    {
        $currentUserId = $request->user()?->id;

        $tweets = Tweet::whereNull('parent_id')
            ->withInteractionDetails($currentUserId)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Real hashtag analytics from last 7 days
        $trends = Hashtag::withCount(['tweets' => function ($q) {
                $q->where('tweets.created_at', '>=', now()->subDays(7));
            }])
            ->having('tweets_count', '>', 0)
            ->orderByDesc('tweets_count')
            ->limit(10)
            ->get()
            ->map(fn ($h) => [
                'category'     => 'Trending in Social',
                'topic'        => '#' . $h->tag,
                'tweets_count' => number_format($h->tweets_count) . ' ' . Str::plural('post', $h->tweets_count),
            ]);

        return Inertia::render('Explore', [
            'tweets' => $tweets,
            'trends' => $trends,
        ]);
    }
}
