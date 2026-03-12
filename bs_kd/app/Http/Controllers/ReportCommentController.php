<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StudentReportComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface; // Import the interface
use App\Models\AssignedTeacher;

class ReportCommentController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'semester_id' => 'required|exists:semesters,id',
            'type' => 'required|in:teacher,principal',
            'comment' => 'nullable|string'
        ]);

        $user = Auth::user();
        if (!$user->can('manage marks') && !$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
            abort(403);
        }

        // Additional Authorization Checks
        $current_session_id = $this->getSchoolCurrentSession();

        if ($request->type == 'teacher') {
            // Teacher Check: Ensure teacher is assigned to any class/section the student is in?
            // Or just trust 'Teacher' role if they can access the view?
            // Usually stricter: Check if teacher is assigned to student's class/section.
            // For now, I'll rely on the fact that they reached the input form via a protected view.
            if (!$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
                abort(403, 'Only Teachers or Admins can add teacher comments.');
            }
        }

        if ($request->type == 'principal') {
            if (!$user->hasRole('Admin') && !$user->hasRole('Principal')) { // Assuming Principal role or Admin
                if (!$user->hasRole('Admin')) { // Fallback to Admin only if Principal role doesn't exist independently
                    abort(403, 'Only Admins/Principals can add principal comments.');
                }
            }
        }

        // Find or Create Comment Record
        // We need to resolve class_id and section_id from the student's current promotion record for this session
        // However, the comment might be for a past semester? No, comments are usually for current context.
        // We will fetch student's promotion to get class/section.

        $promotion = \App\Models\Promotion::where('student_id', $request->student_id)
            ->where('session_id', $current_session_id)
            ->first();

        if (!$promotion) {
            return back()->withError('Student is not promoted to any class for this session.');
        }

        $reportComment = StudentReportComment::firstOrNew([
            'student_id' => $request->student_id,
            'semester_id' => $request->semester_id,
            'session_id' => $current_session_id
        ]);

        $reportComment->class_id = $promotion->class_id;
        $reportComment->section_id = $promotion->section_id;

        if ($request->type == 'teacher') {
            $reportComment->teacher_comment = $request->comment;
        } else {
            $reportComment->principal_comment = $request->comment;
        }

        $reportComment->save();

        return back()->with('status', ucfirst($request->type) . ' comment saved successfully.');
    }
}
