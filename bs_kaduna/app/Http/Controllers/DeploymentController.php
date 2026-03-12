<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class DeploymentController extends Controller
{
    public function migrate(Request $request)
    {
        $configuredKey = env('DEPLOY_MIGRATE_KEY');
        $providedKey = (string) $request->query('key', '');
        $isAdmin = Auth::check() && Auth::user()->hasRole('Admin');

        if (!$isAdmin) {
            if (empty($configuredKey) || !hash_equals($configuredKey, $providedKey)) {
                abort(403, 'Unauthorized migration trigger.');
            }
        }

        Artisan::call('migrate', ['--force' => true]);

        return response()->view('deployment.migrate', [
            'output' => trim(Artisan::output()),
        ]);
    }
}
