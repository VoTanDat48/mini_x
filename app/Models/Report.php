<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'tweet_id',
        'reported_user_id',
        'reason',
        'description',
        'status',
        'admin_note',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Reasons available for reporting.
     */
    public const REASONS = [
        'spam'          => 'Spam',
        'harassment'    => 'Quấy rối / Xúc phạm',
        'hate_speech'   => 'Ngôn ngữ thù địch',
        'misinformation'=> 'Tin giả / Thông tin sai lệch',
        'nsfw'          => 'Nội dung không phù hợp (NSFW)',
        'other'         => 'Khác',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function tweet()
    {
        return $this->belongsTo(Tweet::class);
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // ─── Accessors ───────────────────────────────────────────

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Chờ xử lý',
            'resolved'  => 'Đã giải quyết',
            'dismissed' => 'Đã bác bỏ',
            default     => $this->status,
        };
    }
}

