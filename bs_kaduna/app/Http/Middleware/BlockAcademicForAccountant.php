<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockAcademicForAccountant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is an Accountant (not Admin), block academic routes
        if (Auth::check() && Auth::user()->hasRole('Accountant') && !Auth::user()->hasRole('Admin')) {
            // Define academic route patterns to block
            $blockedPatterns = [
                'attendances/*',
                'marks/*',
                'exams/*',
                'classes/*',
                'sections/*',
                'courses/*',
                'students/create',
                'students/edit/*',
                'students/academic/*',
                'teachers/*',
                'promotions/*',
                'routines/*',
                'syllabuses/*',
                'assignments/*',
                'notices/create',
                'notices/edit/*',
                'academics/*',
            ];

            $currentPath = $request->path();

            foreach ($blockedPatterns as $pattern) {
                if (fnmatch($pattern, $currentPath)) {
                    abort(403, 'Accountants do not have access to academic functions.');
                }
            }
        }

        return $next($request);
    }
}
