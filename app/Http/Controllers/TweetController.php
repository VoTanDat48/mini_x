<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTweetRequest;
use App\Models\Tweet;
use App\Models\TweetView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TweetController extends Controller
{
    /**
     * Display a listing of the top-level tweets.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $userId = $user?->id;
        $tab = $request->query('tab', 'for-you');

        $excludedUserIds = collect();
        if ($user) {
            $blockedIds = $user->blockedUsers()->pluck('blocked_id');
            $blockedByIds = $user->blockedByUsers()->pluck('user_id');
            $mutedIds = $user->mutedUsers()->pluck('muted_id');
            $excludedUserIds = $blockedIds->merge($blockedByIds)->merge($mutedIds)->unique();
        }

        $query = Tweet::whereNull('parent_id')
            ->when($excludedUserIds->isNotEmpty(), fn ($q) => $q->whereNotIn('user_id', $excludedUserIds));

        if ($tab === 'following') {
            if ($user) {
                $followingIds = $user->following()->pluck('following_id');
                // Display posts from followed accounts plus author's own posts
                $feedUserIds = $followingIds->push($user->id)->unique();
                $query->whereIn('user_id', $feedUserIds);
            } else {
                // Guests see an empty following feed
                $query->whereRaw('1 = 0');
            }
        }

        $tweets = $query->withInteractionDetails($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Welcome', [
            'tweets' => $tweets,
            'currentTab' => $tab,
        ]);
    }

    /**
     * Display a single tweet with its replies thread.
     */
    public function show(Request $request, $username, $id): Response
    {
        $userId = $request->user()?->id;

        $tweet = Tweet::where('id', $id)
            ->withInteractionDetails($userId)
            ->firstOrFail();

        // Deduplicated view tracking within 2-hour window
        $ip = $request->ip();
        $sessionId = $request->session()->getId();

        $recentView = TweetView::where('tweet_id', $tweet->id)
            ->where(function ($q) use ($userId, $ip, $sessionId) {
                if ($userId) {
                    $q->where('user_id', $userId);
                } else {
                    $q->where('ip_address', $ip)->orWhere('session_id', $sessionId);
                }
            })
            ->where('created_at', '>=', now()->subHours(2))
            ->first();

        if (! $recentView) {
            TweetView::create([
                'tweet_id' => $tweet->id,
                'user_id' => $userId,
                'ip_address' => $ip,
                'session_id' => $sessionId,
            ]);
        }

        $replies = Tweet::where('parent_id', $tweet->id)
            ->withInteractionDetails($userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Tweet/Show', [
            'tweet' => $tweet,
            'replies' => $replies,
        ]);
    }

    /**
     * Store a newly created tweet or reply in storage.
     */
    public function store(StoreTweetRequest $request): RedirectResponse
    {
        $filePath = null;
        $isVideo = false;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $videoExtensions = ['mp4', 'mov', 'webm'];
            $isVideo = in_array($extension, $videoExtensions);

            $directory = $isVideo ? 'tweets/videos' : 'tweets/images';
            $savedPath = $file->store($directory, 'public');
            $filePath = '/storage/' . $savedPath;
        }

        $parentId = $request->input('parent_id');
        $quoteOfId = $request->input('quote_of_id');

        $newTweet = $request->user()->tweets()->create([
            'parent_id' => $parentId,
            'quote_of_id' => $quoteOfId,
            'tweet' => $request->input('tweet') ?? '',
            'file' => $filePath,
            'is_video' => $isVideo,
        ]);

        // Sync hashtags
        $newTweet->syncHashtags();

        // Notify mentions
        $newTweet->notifyMentions($request->user());

        if ($parentId) {
            $parent = Tweet::find($parentId);
            if ($parent && $parent->user_id !== $request->user()->id && $parent->user) {
                $parent->user->notify(new \App\Notifications\AppActivityNotification($request->user(), 'reply', $newTweet));
            }
            return redirect()->back();
        }

        if ($quoteOfId) {
            $quoted = Tweet::find($quoteOfId);
            if ($quoted && $quoted->user_id !== $request->user()->id && $quoted->user) {
                $quoted->user->notify(new \App\Notifications\AppActivityNotification($request->user(), 'quote', $newTweet));
            }
        }

        return redirect()->route('tweets.index');
    }

    /**
     * Update the specified tweet in storage.
     */
    public function update(Request $request, Tweet $tweet): RedirectResponse
    {
        $this->authorize('update', $tweet);

        $request->validate([
            'tweet' => 'required_without:file|nullable|string|max:280',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:5120',
            'remove_file' => 'nullable|boolean',
        ]);

        $filePath = $tweet->file;
        $isVideo = $tweet->is_video;

        if ($request->boolean('remove_file')) {
            if ($filePath && str_starts_with($filePath, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $filePath));
            }
            $filePath = null;
            $isVideo = false;
        }

        if ($request->hasFile('file')) {
            // Delete old file if present
            if ($filePath && str_starts_with($filePath, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $filePath));
            }
            $uploaded = $request->file('file');
            $storedPath = $uploaded->store('tweets/images', 'public');
            $filePath = '/storage/' . $storedPath;
            $isVideo = false;
        }

        $tweet->update([
            'tweet' => $request->input('tweet') ?? '',
            'file' => $filePath,
            'is_video' => $isVideo,
        ]);

        $tweet->syncHashtags();
        $tweet->notifyMentions($request->user());

        return redirect()->back()->with('success', 'Bài viết đã được cập nhật thành công.');
    }

    /**
     * Remove the specified tweet from storage.
     */
    public function destroy(Tweet $tweet): RedirectResponse
    {
        $this->authorize('delete', $tweet);

        $tweet->delete();

        return redirect()->back();
    }
}
