<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Store a new report (tweet or user).
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:tweet,user',
            'tweet_id'    => 'required_if:type,tweet|nullable|exists:tweets,id',
            'reported_user_id' => 'required_if:type,user|nullable|exists:users,id',
            'reason'      => 'required|in:spam,harassment,hate_speech,misinformation,nsfw,other',
            'description' => 'nullable|string|max:500',
        ]);

        $reporter = $request->user();

        // Prevent reporting own content
        if ($request->type === 'tweet') {
            $tweet = Tweet::findOrFail($request->tweet_id);
            if ($tweet->user_id === $reporter->id) {
                return back()->withErrors(['report' => 'Bạn không thể báo cáo bài viết của chính mình.']);
            }
            // Check duplicate
            $exists = Report::where('reporter_id', $reporter->id)
                ->where('tweet_id', $request->tweet_id)
                ->exists();
            if ($exists) {
                return back()->withErrors(['report' => 'Bạn đã báo cáo bài viết này trước đó.']);
            }
            Report::create([
                'reporter_id' => $reporter->id,
                'tweet_id'    => $request->tweet_id,
                'reason'      => $request->reason,
                'description' => $request->description,
            ]);
        } else {
            // user report
            if ((int) $request->reported_user_id === $reporter->id) {
                return back()->withErrors(['report' => 'Bạn không thể báo cáo tài khoản của chính mình.']);
            }
            $exists = Report::where('reporter_id', $reporter->id)
                ->where('reported_user_id', $request->reported_user_id)
                ->exists();
            if ($exists) {
                return back()->withErrors(['report' => 'Bạn đã báo cáo tài khoản này trước đó.']);
            }
            Report::create([
                'reporter_id'      => $reporter->id,
                'reported_user_id' => $request->reported_user_id,
                'reason'           => $request->reason,
                'description'      => $request->description,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Báo cáo đã được gửi thành công.']);
        }

        return back()->with('success', 'Cảm ơn! Báo cáo của bạn đã được ghi nhận.');
    }
}

