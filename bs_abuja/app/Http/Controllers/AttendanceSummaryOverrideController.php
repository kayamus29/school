<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Semester;
use App\Models\AssignedTeacher;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Models\AttendanceSummaryOverride;
use Illuminate\Support\Facades\Auth;

class AttendanceSummaryOverrideController extends Controller
{
    use SchoolSession;

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'semester_id' => 'required|exists:semesters,id',
            'days_present' => 'required|integer|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        $sessionId = $this->getSchoolCurrentSession();
        $semester = Semester::where('id', $request->semester_id)
            ->where('session_id', $sessionId)
            ->firstOrFail();

        if ($request->days_present > (int) ($semester->total_school_days ?? 0)) {
            return back()->withError('Days present cannot be greater than the total school days for the term.');
        }

        $promotion = Promotion::where('student_id', $request->student_id)
            ->where('session_id', $sessionId)
            ->firstOrFail();

        $isClassTeacher = AssignedTeacher::query()
            ->where('teacher_id', Auth::id())
            ->where('session_id', $sessionId)
            ->where('class_id', $promotion->class_id)
            ->where('section_id', $promotion->section_id)
            ->sectionLeadership()
            ->exists();

        if (!$isClassTeacher) {
            abort(403, 'Only the section teacher or class supervisor can override attendance summary.');
        }

        AttendanceSummaryOverride::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'semester_id' => $request->semester_id,
                'session_id' => $sessionId,
            ],
            [
                'class_id' => $promotion->class_id,
                'section_id' => $promotion->section_id,
                'days_present' => $request->days_present,
                'updated_by' => Auth::id(),
                'note' => $request->note,
            ]
        );

        return back()->with('status', 'Attendance summary override saved successfully.');
    }
}
