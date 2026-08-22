<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CapabilityMiddleware
{
    public function handle(Request $request, Closure $next, string $capabilities): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('home');
        }

        // Admin bypasses all checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        $required = collect(explode(',', $capabilities))
            ->map(fn($c) => trim($c))
            ->filter()
            ->all();

        foreach ($required as $capability) {
            if ($user->role === $capability || $user->hasCapability($capability)) {
                return $next($request);
            }
        }

        // Fallback redirects
        $redirectRoute = match ($user->role) {
            'hod' => 'hod.dashboard',
            'admin' => 'admin.dashboard',
            'student' => 'student.dashboard',
            'cit' => 'cit.dashboard',
            'coordinator' => 'coordinator.dashboard',
            'idcard_officer' => 'idcard.processing',
            'lecturer' => 'lecturer.dashboard',
            'exam_officer' => 'exam-officer.dashboard',
            default => 'analytics',
        };

        if ($request->route()?->getName() !== $redirectRoute) {
            return redirect()->route($redirectRoute);
        }

        return $next($request);
    }
}
