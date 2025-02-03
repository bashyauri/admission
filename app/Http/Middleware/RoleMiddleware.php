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
        $userRole = $request->user()->role;

        if ($userRole !== $role) {
            $redirectRoute = $this->getRedirectRoute($userRole);
        }

        return $next($request);
    }

    /**
     * Get the redirect route based on the user's role.
     *
     * @param  string  $role
     * @return string
     */
    private function getRedirectRoute(string $role): string
    {
        return match ($role) {
            'hod' => 'hod.dashboard',
            'admin' => 'admin.dashboard',
            'student' => 'student.dashboard',
            'cit' => 'cit.dashboard',
            'coordinator' => 'coordinator.dashboard',
            default => 'analytics',
        };
    }
}
