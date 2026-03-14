<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Interfaces\WalletServiceInterface::class,
            \App\Services\WalletService::class
        );

        $this->app->bind('path.public', function () {
            return base_path('../public_html');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share site settings globally with all views
        view()->composer('*', function ($view) {
            $site_setting = null;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
                    $site_setting = \App\Models\SiteSetting::first();
                }
            } catch (\Exception $e) {
                // Ignore DB errors during boot (e.g. before migrations)
            }

            if (!$site_setting) {
                // Fallback to default object if DB record is missing
                $site_setting = (object) [
                    'school_name' => config('app.name', 'Unifiedtransform'),
                    'primary_color' => '#3490dc',
                    'secondary_color' => '#ffffff',
                    'school_logo_path' => null,
                    'login_background_path' => null,
                    'office_lat' => 6.5244,
                    'office_long' => 3.3792,
                    'geo_range' => 500,
                    'late_time' => '08:00',
                ];
            }

            $view->with('site_setting', $site_setting);
        });
    }
}
