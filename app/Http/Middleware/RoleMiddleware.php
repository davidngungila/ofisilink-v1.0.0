<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Check if user is blocked
        if ($user->blocked_at) {
            $isBlocked = false;
            if (!$user->blocked_until) {
                // Forever blocked
                $isBlocked = true;
            } else {
                // Check if block period has expired
                $isBlocked = now()->isBefore($user->blocked_until);
                if (!$isBlocked) {
                    // Block period expired, unblock user
                    $user->update([
                        'blocked_at' => null,
                        'blocked_until' => null,
                        'block_reason' => null,
                        'blocked_by' => null,
                    ]);
                }
            }
            
            if ($isBlocked) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                $message = 'Your account has been blocked.';
                if ($user->blocked_until) {
                    $message .= ' Blocked until: ' . $user->blocked_until->format('Y-m-d H:i:s');
                } else {
                    $message .= ' This is a permanent block.';
                }
                if ($user->block_reason) {
                    $message .= ' Reason: ' . $user->block_reason;
                }
                
                return redirect()->route('login')->withErrors(['email' => $message]);
            }
        }
        
        // System Admin has access to everything
        if ($user->hasRole('System Admin')) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Access denied. You do not have the required role.');
        }

        return $next($request);
    }
}