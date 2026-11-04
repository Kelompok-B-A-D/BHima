<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DashboardAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return $request->expectsJson() ? response()->json(['message' => 'Silakan masuk.'], 401) : redirect()->route('login');
        }
        abort_unless($user->is_active && in_array($user->role, ['admin', 'verifikator'], true), 403);
        $role = $request->route('role');
        abort_if($role && $role !== $user->role, 403);
        $page = $request->route('page') ?? 'dashboard';
        if ($user->role === 'verifikator') {
            abort_unless(in_array($page, ['dashboard', 'pengaduan', 'aktivitas'], true), 403);
        }

        return $next($request);
    }
}
