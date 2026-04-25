<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access the admin panel.');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Your account has been deactivated.');
        }

        if ($role === 'super_admin' && !$user->isSuperAdmin()) {
            abort(403, 'You do not have permission to access this area.');
        }

        if (!$user->isAdmin()) {
            Auth::logout();
            return redirect()->route('admin.login')->with('error', 'Insufficient permissions.');
        }

        // Session timeout: 2 hours
        $lastActivity = session('last_activity');
        if ($lastActivity && (time() - $lastActivity > 7200)) {
            Auth::logout();
            session()->invalidate();
            return redirect()->route('admin.login')->with('error', 'Session expired. Please login again.');
        }
        session(['last_activity' => time()]);

        return $next($request);
    }
}

// ─────────────────────────────────────────────
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        return $response;
    }
}

// ─────────────────────────────────────────────
class MaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (\App\Models\Setting::get('maintenance_mode', '0') === '1') {
            if (!$request->routeIs('admin.*') && !Auth::check()) {
                return response()->view('store.maintenance', [], 503);
            }
        }
        return $next($request);
    }
}
