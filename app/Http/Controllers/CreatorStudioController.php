<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CreatorStudioController extends Controller
{
    /**
     * Display Creator Studio page.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $followersCount = $user->followers()->count();
        $tweetsCount = $user->tweets()->count();
        
        // Sum total likes and retweets received on user's posts
        $userTweetIds = $user->tweets()->pluck('id');
        $totalLikes = \App\Models\Like::whereIn('tweet_id', $userTweetIds)->count();
        $totalRetweets = \App\Models\Retweet::whereIn('tweet_id', $userTweetIds)->count();

        // Criteria for monetization: at least 500 followers
        $isEligible = $followersCount >= 500;

        return Inertia::render('CreatorStudio/Index', [
            'creatorStats' => [
                'followers_count' => $followersCount,
                'tweets_count'    => $tweetsCount,
                'total_likes'     => $totalLikes,
                'total_retweets'  => $totalRetweets,
                'is_eligible'     => $isEligible,
                'required_followers' => 500,
            ],
        ]);
    }
}

