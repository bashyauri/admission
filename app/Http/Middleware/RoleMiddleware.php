<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $allowed = collect(explode(',', $roles))->map(fn($r) => trim($r))->filter()->all();
        $userRole = $user->role ?? null;

        if (!$userRole || !in_array($userRole, $allowed, true)) {
            $redirectRoute = match ($userRole) {
                'hod' => 'hod.dashboard',
                'admin' => 'admin.dashboard',
                'student' => 'student.dashboard',
                'cit' => 'cit.dashboard',
                'coordinator' => 'coordinator.dashboard',
                'idcard_officer' => 'idcard.processing',
                default => 'analytics',
            };

            if ($request->route()?->getName() !== $redirectRoute) {
                return redirect()->route($redirectRoute);
            }
        }

        return $next($request);
    }
}
