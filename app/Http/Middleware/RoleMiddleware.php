<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if ($request->user()->role !== $role) {
            $redirectRoute = match ($request->user()->role) {
                'hod' => 'hod.dashboard',
                'admin' => 'admin.dashboard',
                'student' => 'student.dashboard',
                default => 'analytics',
            };

            return redirect()->route($redirectRoute);
        }

        return $next($request);
    }
}