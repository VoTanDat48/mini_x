<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'quote_of_id',
        'tweet',
        'file',
        'is_video',
    ];

    protected $casts = [
        'is_video' => 'boolean',
        'is_liked' => 'boolean',
        'is_retweeted' => 'boolean',
        'is_bookmarked' => 'boolean',
    ];

    protected $appends = [
        'created_at_human',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Tweet::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Tweet::class, 'parent_id');
    }

    public function quoteOf()
    {
        return $this->belongsTo(Tweet::class, 'quote_of_id');
    }

    public function quotes()
    {
        return $this->hasMany(Tweet::class, 'quote_of_id');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function retweets()
    {
        return $this->hasMany(Retweet::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function views()
    {
        return $this->hasMany(TweetView::class);
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'hashtag_tweet')->withTimestamps();
    }

    public function scopeWithInteractionDetails($query, $userId = null)
    {
        return $query->with(['user', 'quoteOf.user'])
            ->withCount(['replies', 'retweets', 'likes', 'views'])
            ->when($userId, function ($q) use ($userId) {
                $q->withExists([
                    'likes as is_liked' => fn ($subQuery) => $subQuery->where('user_id', $userId),
                    'retweets as is_retweeted' => fn ($subQuery) => $subQuery->where('user_id', $userId),
                    'bookmarks as is_bookmarked' => fn ($subQuery) => $subQuery->where('user_id', $userId),
                ]);
            });
    }

    public function getCreatedAtHumanAttribute(): string
    {
        if (! $this->created_at) {
            return '';
        }

        return $this->created_at->diffForHumans();
    }

    /**
     * Parse and sync hashtags found in tweet body.
     */
    public function syncHashtags(): void
    {
        if (empty($this->tweet)) {
            $this->hashtags()->detach();
            return;
        }

        preg_match_all('/#([a-zA-Z0-9_\x{00C0}-\x{024F}\x{1E00}-\x{1EFF}]+)/u', $this->tweet, $matches);
        $tags = collect($matches[1] ?? [])
            ->map(fn ($tag) => mb_strtolower($tag))
            ->unique()
            ->filter();

        $hashtagIds = [];
        foreach ($tags as $tag) {
            $hashtag = Hashtag::firstOrCreate(['tag' => $tag]);
            $hashtagIds[] = $hashtag->id;
        }

        $this->hashtags()->sync($hashtagIds);
    }

    /**
     * Parse @mentions and notify mentioned active users.
     */
    public function notifyMentions(User $author): void
    {
        if (empty($this->tweet)) {
            return;
        }

        preg_match_all('/@([a-zA-Z0-9_]{3,15})/', $this->tweet, $matches);
        $usernames = collect($matches[1] ?? [])
            ->unique()
            ->filter(fn ($uname) => strtolower($uname) !== strtolower($author->username));

        if ($usernames->isEmpty()) {
            return;
        }

        $usersToNotify = User::whereIn('username', $usernames)
            ->where('status', 'active')
            ->get();

        foreach ($usersToNotify as $mentionedUser) {
            // Don't duplicate notification if already replied
            if ($this->parent && $this->parent->user_id === $mentionedUser->id) {
                continue;
            }
            if ($mentionedUser->hasBlocked($author) || $author->hasBlocked($mentionedUser)) {
                continue;
            }

            $mentionedUser->notify(new \App\Notifications\AppActivityNotification($author, 'mention', $this));
        }
    }

    /**
     * The "booted" method of the model.
     * Clean up attached media file from storage on tweet deletion.
     */
    protected static function booted()
    {
        static::deleting(function (Tweet $tweet) {
            if (! empty($tweet->file)) {
                if (str_starts_with($tweet->file, '/storage/')) {
                    $relativePath = str_replace('/storage/', '', $tweet->file);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
                } elseif (file_exists(public_path($tweet->file))) {
                    @unlink(public_path($tweet->file));
                }
            }
        });
    }
}
