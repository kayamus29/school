<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\StudentParentInfo;

class ParentPortalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:Parent']);
    }

    /**
     * Parent Dashboard
     */
    public function dashboard()
    {
        $parent = Auth::user();
        $children = StudentParentInfo::where('parent_user_id', $parent->id)->with('student')->get();

        return view('parent.dashboard', compact('parent', 'children'));
    }

    /**
     * View Child Dashboard (As Parent)
     */
    public function childDashboard($student_id)
    {
        $parent = Auth::user();

        // Verify this is my child
        $isMyChild = StudentParentInfo::where('parent_user_id', $parent->id)
            ->where('student_id', $student_id)
            ->exists();

        if (!$isMyChild) {
            abort(403, "This student is not linked to your account.");
        }

        $student = User::findOrFail($student_id);

        // Return view (reusing student dashboard or specific parent view)
        return view('parent.child-dashboard', compact('parent', 'student'));
    }
}
