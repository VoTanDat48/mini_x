<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TweetView extends Model
{
    use HasFactory;

    protected $fillable = [
        'tweet_id',
        'user_id',
        'ip_address',
        'session_id',
    ];

    public function tweet()
    {
        return $this->belongsTo(Tweet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
