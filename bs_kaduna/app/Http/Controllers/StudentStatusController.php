<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Admin']);
    }

    /**
     * Deactivate a student.
     */
    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $student = User::findOrFail($id);

        if ($student->status === 'deactivated') {
            return back()->with('error', 'Student is already deactivated.');
        }

        DB::transaction(function () use ($student, $request) {
            $student->update([
                'status' => 'deactivated',
                'deactivated_at' => now(),
                'deactivated_by' => Auth::id(),
                'deactivation_reason' => $request->reason,
            ]);
        });

        return back()->with('status', 'Student deactivated successfully.');
    }

    /**
     * Reactivate a student.
     */
    public function reactivate($id)
    {
        $student = User::findOrFail($id);

        if ($student->status === 'active') {
            return back()->with('error', 'Student is already active.');
        }

        DB::transaction(function () use ($student) {
            $student->update([
                'status' => 'active',
                'deactivated_at' => null,
                'deactivated_by' => null,
                'deactivation_reason' => null,
            ]);
        });

        return back()->with('status', 'Student reactivated successfully.');
    }
}
