<?php

namespace App\Notifications;

use App\Models\Tweet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AppActivityNotification extends Notification
{
    use Queueable;

    public User $sender;
    public string $type; // 'like' | 'retweet' | 'reply' | 'follow'
    public ?Tweet $tweet;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $sender, string $type, ?Tweet $tweet = null)
    {
        $this->sender = $sender;
        $this->type = $type;
        $this->tweet = $tweet;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->type,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'sender_username' => $this->sender->username,
            'sender_avatar' => $this->sender->avatar_url,
            'tweet_id' => $this->tweet?->id,
            'tweet_text' => $this->tweet ? Str::limit($this->tweet->tweet, 80) : null,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
