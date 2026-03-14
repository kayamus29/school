<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Admin') ? true : null;
        });

        // Composite Gate for Student List Access (Fixes Middleware Pipe Issue)
        Gate::define('view-student-list', function ($user) {
            return $user->can('view users') || $user->can('view assigned students');
        });

        // Composite Gate for Attendance Access
        Gate::define('view-attendance-pages', function ($user) {
            return $user->can('view attendances') || $user->can('take attendance');
        });
    }
}
