<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TweetController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\BlockMuteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\BotController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CreatorStudioController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

// Trang chủ hiển thị danh sách Tweets
Route::get('/', [TweetController::class, 'index'])->name('tweets.index');

// Gửi bài viết mới
Route::post('/tweets', [TweetController::class, 'store'])->name('tweets.store');

// Xóa bài viết theo ID
Route::delete('/tweets/{id}', [TweetController::class, 'destroy'])->name('tweets.destroy');

// Các route profile và xác thực của Breeze
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/recipients', [MessageController::class, 'recipients'])->name('messages.recipients');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');
    Route::post('/users/{user}/block', [BlockMuteController::class, 'toggleBlock'])->name('users.block');
    Route::post('/users/{user}/mute', [BlockMuteController::class, 'toggleMute'])->name('users.mute');

    Route::post('/profile/update', [UserProfileController::class, 'update'])->name('profile.custom_update');

    Route::get('/bot', [BotController::class, 'index'])->name('bot.index');
    Route::post('/bot/chat', [BotController::class, 'chat'])->name('bot.chat');
    Route::post('/bot/clear', [BotController::class, 'clear'])->name('bot.clear');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');

    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/account', [SettingsController::class, 'account'])->name('account');
        Route::get('/privacy', [SettingsController::class, 'privacy'])->name('privacy');
        Route::get('/privacy/blocked', [SettingsController::class, 'blockedUsers'])->name('privacy.blocked');
        Route::get('/privacy/muted', [SettingsController::class, 'mutedUsers'])->name('privacy.muted');
        Route::patch('/privacy', [SettingsController::class, 'updatePrivacy'])->name('privacy.update');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::patch('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/export-data', [SettingsController::class, 'exportData'])->name('export');
        Route::post('/deactivate', [SettingsController::class, 'deactivate'])->name('deactivate');
    });

    Route::get('/creator-studio', [CreatorStudioController::class, 'index'])->name('creator_studio');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::post('/reports/{report}/resolve', [AdminDashboardController::class, 'resolveReport'])->name('reports.resolve');
    Route::delete('/reports/{report}/tweet', [AdminDashboardController::class, 'deleteTweetFromReport'])->name('reports.delete_tweet');
    Route::post('/users/{user}/toggle-status', [AdminDashboardController::class, 'toggleUserStatus'])->name('users.toggle_status');
    Route::post('/users/{user}/toggle-role', [AdminDashboardController::class, 'toggleUserRole'])->name('users.toggle_role');
});

Route::get('/{username}/followers', [FollowController::class, 'followers'])->name('users.followers');
Route::get('/{username}/following', [FollowController::class, 'following'])->name('users.following');

require __DIR__.'/auth.php';