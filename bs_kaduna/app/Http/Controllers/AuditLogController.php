<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:view audit logs']);
    }

    public function index()
    {
        $logs = Activity::with('causer')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('audit.index', compact('logs'));
    }

    public function show($id)
    {
        $log = Activity::with('causer', 'subject')->findOrFail($id);
        return view('audit.show', compact('log'));
    }
}
