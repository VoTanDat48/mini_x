<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSuspended
{
    /**
     * Block suspended accounts from performing any mutations.
     * GET requests (viewing) are still allowed.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->status === 'suspended' && ! $request->isMethod('GET')) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị đình chỉ. Vui lòng liên hệ quản trị viên.',
                ], 403);
            }
            return back()->withErrors(['account' => 'Tài khoản của bạn đã bị đình chỉ.']);
        }

        return $next($request);
    }
}

