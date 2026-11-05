<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Redirect to the default settings tab (Your Account).
     */
    public function index()
    {
        return redirect()->route('settings.account');
    }

    /**
     * Display Your Account settings.
     */
    public function account(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Settings/Account', [
            'accountInfo' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'email'      => $user->email,
                'joined'     => $user->created_at ? $user->created_at->format('F d, Y') : '',
                'status'     => $user->status,
                'role'       => $user->role,
            ],
            'userSettings' => $user->all_settings,
        ]);
    }

    /**
     * Display Privacy and safety settings.
     */
    public function privacy(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Settings/Privacy', [
            'userSettings'  => $user->all_settings,
            'blockedCount'  => $user->blockedUsers()->count(),
            'mutedCount'    => $user->mutedUsers()->count(),
        ]);
    }

    /**
     * Get list of blocked users.
     */
    public function blockedUsers(Request $request)
    {
        $blocked = $request->user()->blockedUsers()
            ->select('users.id', 'users.name', 'users.username', 'users.avatar_path', 'users.bio')
            ->get();

        return response()->json($blocked);
    }

    /**
     * Get list of muted users.
     */
    public function mutedUsers(Request $request)
    {
        $muted = $request->user()->mutedUsers()
            ->select('users.id', 'users.name', 'users.username', 'users.avatar_path', 'users.bio')
            ->get();

        return response()->json($muted);
    }

    /**
     * Update Privacy and safety settings.
     */
    public function updatePrivacy(Request $request)
    {
        $validated = $request->validate([
            'allow_dms_from' => 'nullable|in:everyone,following',
            'protect_posts'  => 'nullable|boolean',
        ]);

        $user = $request->user();
        $currentSettings = $user->settings ?? [];
        $user->settings = array_merge($currentSettings, $validated);
        $user->save();

        return back()->with('message', 'Privacy settings updated successfully.');
    }

    /**
     * Display Notifications settings.
     */
    public function notifications(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Settings/Notifications', [
            'userSettings' => $user->all_settings,
        ]);
    }

    /**
     * Update Notifications preferences.
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'quality_filter'  => 'nullable|boolean',
            'notify_likes'    => 'nullable|boolean',
            'notify_retweets' => 'nullable|boolean',
            'notify_follows'  => 'nullable|boolean',
            'notify_mentions' => 'nullable|boolean',
            'notify_email'    => 'nullable|boolean',
        ]);

        $user = $request->user();
        $currentSettings = $user->settings ?? [];
        $user->settings = array_merge($currentSettings, $validated);
        $user->save();

        return back()->with('message', 'Notification preferences updated successfully.');
    }

    /**
     * Export / Download archive of user data in JSON format.
     */
    public function exportData(Request $request)
    {
        $user = $request->user();

        $data = [
            'account' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'email'      => $user->email,
                'bio'        => $user->bio,
                'location'   => $user->location,
                'website'    => $user->website,
                'joined_at'  => $user->created_at ? $user->created_at->toIso8601String() : null,
            ],
            'statistics' => [
                'tweets_count'    => $user->tweets()->count(),
                'following_count' => $user->following()->count(),
                'followers_count' => $user->followers()->count(),
                'likes_count'     => $user->likes()->count(),
                'bookmarks_count' => $user->bookmarks()->count(),
            ],
            'recent_tweets' => $user->tweets()->latest()->limit(50)->get(['id', 'tweet', 'created_at']),
            'exported_at'   => now()->toIso8601String(),
        ];

        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $fileName = 'twitter-data-' . $user->username . '-' . date('Y-m-d') . '.json';

        return response($jsonContent, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Deactivate user account.
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password entered.']);
        }

        $user->deactivated_at = now();
        $user->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'Your account has been deactivated.');
    }
}

