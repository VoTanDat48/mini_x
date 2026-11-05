<?php

namespace App\Http\Controllers;

use App\Models\Retweet;
use App\Models\Tweet;
use Illuminate\Http\Request;

class RetweetController extends Controller
{
    /**
     * Toggle retweet status for the specified tweet.
     */
    public function toggle(Request $request, Tweet $tweet)
    {
        $userId = $request->user()->id;
        $existing = Retweet::where('user_id', $userId)
            ->where('tweet_id', $tweet->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $retweeted = false;
        } else {
            Retweet::create([
                'user_id' => $userId,
                'tweet_id' => $tweet->id,
            ]);
            $retweeted = true;

            // Notify tweet author if not retweeting own tweet
            if ($tweet->user_id !== $userId && $tweet->user) {
                $tweet->user->notify(new \App\Notifications\AppActivityNotification($request->user(), 'retweet', $tweet));
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'retweeted' => $retweeted,
                'retweets_count' => $tweet->retweets()->count(),
            ]);
        }

        return back();
    }
}
