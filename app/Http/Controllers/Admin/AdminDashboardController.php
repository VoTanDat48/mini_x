<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Main stats overview.
     */
    public function index(): Response
    {
        $stats = [
            'total_users'       => User::count(),
            'total_tweets'      => Tweet::count(),
            'pending_reports'   => Report::where('status', 'pending')->count(),
            'suspended_users'   => User::where('status', 'suspended')->count(),
            'new_users_today'   => User::whereDate('created_at', today())->count(),
            'new_tweets_today'  => Tweet::whereDate('created_at', today())->count(),
        ];

        $recentReports = Report::with(['reporter', 'tweet.user', 'reportedUser'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($r) => $this->formatReport($r));

        $recentUsers = User::latest()->limit(5)->get();

        return Inertia::render('Admin/Dashboard', [
            'stats'         => $stats,
            'recentReports' => $recentReports,
            'recentUsers'   => $recentUsers,
        ]);
    }

    /**
     * Manage reports.
     */
    public function reports(Request $request): Response
    {
        $status = $request->get('status', 'pending');
        $type   = $request->get('type', 'all'); // all | tweet | user

        $query = Report::with(['reporter', 'tweet.user', 'reportedUser', 'resolver'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($type === 'tweet', fn ($q) => $q->whereNotNull('tweet_id')->whereNull('reported_user_id'))
            ->when($type === 'user', fn ($q) => $q->whereNotNull('reported_user_id')->whereNull('tweet_id'))
            ->latest()
            ->paginate(20);

        $query->getCollection()->transform(fn ($r) => $this->formatReport($r));

        return Inertia::render('Admin/Reports', [
            'reports'       => $query,
            'currentStatus' => $status,
            'currentType'   => $type,
            'reasons'       => Report::REASONS,
        ]);
    }

    /**
     * Manage users.
     */
    public function users(Request $request): Response
    {
        $search = $request->get('search', '');

        $users = User::withCount(['tweets', 'followers', 'following', 'reportsReceived'])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Users', [
            'users'  => $users,
            'search' => $search,
        ]);
    }

    /**
     * Resolve a report: dismiss or mark resolved.
     */
    public function resolveReport(Request $request, Report $report)
    {
        $request->validate([
            'action'     => 'required|in:resolved,dismissed',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $report->update([
            'status'      => $request->action,
            'admin_note'  => $request->admin_note,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Báo cáo đã được xử lý.');
    }

    /**
     * Delete the tweet associated with a report and resolve the report.
     */
    public function deleteTweetFromReport(Request $request, Report $report)
    {
        $request->validate(['admin_note' => 'nullable|string|max:1000']);

        if ($report->tweet) {
            $report->tweet->delete();
        }

        $report->update([
            'status'      => 'resolved',
            'admin_note'  => $request->admin_note ?? 'Bài viết vi phạm đã bị xóa bởi quản trị viên.',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Bài viết đã bị xóa và báo cáo đã được đánh dấu giải quyết.');
    }

    /**
     * Toggle user suspend / activate.
     */
    public function toggleUserStatus(Request $request, User $user)
    {
        $admin = $request->user();

        // Cannot suspend yourself or another admin
        if ($user->id === $admin->id) {
            return back()->withErrors(['admin' => 'Bạn không thể tự đình chỉ tài khoản của mình.']);
        }
        if ($user->role === 'admin') {
            return back()->withErrors(['admin' => 'Không thể đình chỉ tài khoản quản trị viên khác.']);
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $message = $newStatus === 'suspended'
            ? "Tài khoản @{$user->username} đã bị đình chỉ."
            : "Tài khoản @{$user->username} đã được kích hoạt lại.";

        return back()->with('success', $message);
    }

    /**
     * Toggle user role between admin/user.
     */
    public function toggleUserRole(Request $request, User $user)
    {
        $admin = $request->user();

        if ($user->id === $admin->id) {
            return back()->withErrors(['admin' => 'Bạn không thể thay đổi quyền của chính mình.']);
        }

        $newRole = $user->role === 'admin' ? 'user' : 'admin';
        $user->update(['role' => $newRole]);

        $message = $newRole === 'admin'
            ? "Đã nâng @{$user->username} lên Admin."
            : "Đã hạ @{$user->username} về User.";

        return back()->with('success', $message);
    }

    /**
     * Admin delete any tweet (not tied to a report).
     */
    public function deleteTweet(Request $request, Tweet $tweet)
    {
        $tweet->delete();
        return back()->with('success', 'Bài viết đã bị xóa bởi quản trị viên.');
    }

    // ─── Private helpers ─────────────────────────────────────

    private function formatReport(Report $report): array
    {
        return [
            'id'              => $report->id,
            'reason'          => $report->reason,
            'reason_label'    => $report->reason_label,
            'description'     => $report->description,
            'status'          => $report->status,
            'status_label'    => $report->status_label,
            'admin_note'      => $report->admin_note,
            'created_at'      => $report->created_at?->diffForHumans(),
            'resolved_at'     => $report->resolved_at?->diffForHumans(),
            'reporter'        => $report->reporter ? [
                'id'         => $report->reporter->id,
                'name'       => $report->reporter->name,
                'username'   => $report->reporter->username,
                'avatar_url' => $report->reporter->avatar_url,
            ] : null,
            'resolver'        => $report->resolver ? [
                'id'       => $report->resolver->id,
                'name'     => $report->resolver->name,
                'username' => $report->resolver->username,
            ] : null,
            'tweet'           => $report->tweet ? [
                'id'         => $report->tweet->id,
                'tweet'      => $report->tweet->tweet,
                'file'       => $report->tweet->file,
                'created_at' => $report->tweet->created_at_human,
                'user'       => $report->tweet->user ? [
                    'id'         => $report->tweet->user->id,
                    'name'       => $report->tweet->user->name,
                    'username'   => $report->tweet->user->username,
                    'avatar_url' => $report->tweet->user->avatar_url,
                ] : null,
            ] : null,
            'reported_user'   => $report->reportedUser ? [
                'id'         => $report->reportedUser->id,
                'name'       => $report->reportedUser->name,
                'username'   => $report->reportedUser->username,
                'avatar_url' => $report->reportedUser->avatar_url,
                'status'     => $report->reportedUser->status,
            ] : null,
        ];
    }
}

