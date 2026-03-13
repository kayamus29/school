<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StudentReportComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface; // Import the interface
use App\Models\AssignedTeacher;
use App\Models\Promotion;

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

        $promotion = Promotion::where('student_id', $request->student_id)
            ->where('session_id', $current_session_id)
            ->first();

        if (!$promotion) {
            return back()->withError('Student is not promoted to any class for this session.');
        }

        if ($request->type === 'teacher') {
            if (!$user->hasRole('Teacher') && !$user->hasRole('Admin')) {
                abort(403, 'Only Teachers or Admins can add teacher comments.');
            }

            if ($user->hasRole('Teacher')) {
                $this->authorizeSectionLeadership($user->id, $current_session_id, $promotion->class_id, $promotion->section_id, 'Only the assigned section teacher or class supervisor can add teacher comments.');
            }
        }

        if ($request->type === 'principal') {
            if ($user->hasRole('Admin') || $user->hasRole('Principal')) {
                // allowed
            } elseif ($user->hasRole('Teacher')) {
                $this->authorizeSectionLeadership($user->id, $current_session_id, $promotion->class_id, $promotion->section_id, 'Only the assigned section teacher or class supervisor can add principal comments.');
            } else {
                abort(403, 'Only Admins, Principals, or assigned teachers can add principal comments.');
            }
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

    public function storeAffectiveScores(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'semester_id' => 'required|exists:semesters,id',
            'scores' => 'required|array',
            'scores.punctuality' => 'required|integer|min:1|max:5',
            'scores.neatness' => 'required|integer|min:1|max:5',
            'scores.politeness' => 'required|integer|min:1|max:5',
            'scores.honesty' => 'required|integer|min:1|max:5',
            'scores.performance' => 'required|integer|min:1|max:5',
            'scores.attentiveness' => 'required|integer|min:1|max:5',
            'scores.perseverance' => 'required|integer|min:1|max:5',
            'scores.speaking' => 'required|integer|min:1|max:5',
            'scores.writing' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();
        if (!$user->hasRole('Teacher')) {
            abort(403);
        }

        $current_session_id = $this->getSchoolCurrentSession();
        $promotion = Promotion::where('student_id', $request->student_id)
            ->where('session_id', $current_session_id)
            ->first();

        if (!$promotion) {
            return back()->withError('Student is not promoted to any class for this session.');
        }

        $this->authorizeSectionLeadership($user->id, $current_session_id, $promotion->class_id, $promotion->section_id, 'Only the section teacher or class supervisor can score affective areas.');

        $reportComment = StudentReportComment::firstOrNew([
            'student_id' => $request->student_id,
            'semester_id' => $request->semester_id,
            'session_id' => $current_session_id,
        ]);

        $reportComment->class_id = $promotion->class_id;
        $reportComment->section_id = $promotion->section_id;
        $reportComment->affective_scores = $request->scores;
        $reportComment->save();

        return back()->with('status', 'Affective area scores saved successfully.');
    }

    private function authorizeSectionLeadership(int $teacherId, int $sessionId, int $classId, int $sectionId, string $message): void
    {
        $isAssigned = AssignedTeacher::query()
            ->where('teacher_id', $teacherId)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->forSectionAccess($sectionId)
            ->sectionLeadership()
            ->exists();

        if (!$isAssigned) {
            abort(403, $message);
        }
    }
}
