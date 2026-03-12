<?php

namespace App\Services;

use App\Models\FinalMark;
use App\Models\Promotion;
use App\Models\PromotionPolicy;
use App\Models\PromotionReview;
use App\Models\Semester;
use App\Models\User;
use App\Models\Promotion as PromotionModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    /**
     * Calculate performance for a single student in a session.
     */
    public function calculateStudentPerformance(int $studentId, int $sessionId)
    {
        $semesters = Semester::where('session_id', $sessionId)->orderBy('id')->get();
        $finalMarks = FinalMark::with('course')
            ->where('student_id', $studentId)
            ->where('session_id', $sessionId)
            ->get();

        $courseGroups = $finalMarks->groupBy('course_id');
        $performance = [
            'total_avg' => 0,
            'courses' => [],
            'incomplete' => false
        ];

        if ($courseGroups->isEmpty()) {
            return $performance;
        }

        $allCoursesAvg = 0;
        foreach ($courseGroups as $courseId => $marks) {
            $courseObj = $marks->first()->course;

            // Calculate annual average for this course
            $courseTotal = 0;
            $termsWithMarks = 0;
            $termBreakdown = [];

            foreach ($semesters as $semester) {
                $mark = $marks->where('semester_id', $semester->id)->first();
                $score = $mark ? (float) $mark->final_marks : null;
                $termBreakdown[$semester->id] = $score;

                if ($score !== null) {
                    $courseTotal += $score;
                    $termsWithMarks++;
                }
            }

            $annualAvg = $termsWithMarks > 0 ? ($courseTotal / $termsWithMarks) : 0;
            $allCoursesAvg += $annualAvg;

            $performance['courses'][$courseId] = [
                'course_name' => $courseObj->course_name ?? 'Unknown',
                'annual_avg' => $annualAvg,
                'term_breakdown' => $termBreakdown,
                'is_passed' => $annualAvg >= 50 // Default threshold check
            ];
        }

        $performance['total_avg'] = $allCoursesAvg / count($performance['courses']);

        return $performance;
    }

    /**
     * Evaluate status based on policy.
     */
    public function evaluateStatus(array $performance, PromotionPolicy $policy)
    {
        if (empty($performance['courses'])) {
            return [
                'status' => 'retained',
                'violations' => ['No courses found for student']
            ];
        }

        $status = 'promoted';
        $ruleViolations = [];

        // 1. Check Threshold
        if ($performance['total_avg'] < $policy->passing_threshold) {
            $status = 'retained';
            $ruleViolations[] = "Average {$performance['total_avg']}% is below threshold {$policy->passing_threshold}%";
        }

        // 2. Check Mandatory Courses
        if ($policy->mandatory_course_ids && is_array($policy->mandatory_course_ids) && count($policy->mandatory_course_ids) > 0) {
            foreach ($policy->mandatory_course_ids as $courseId) {
                $courseData = $performance['courses'][$courseId] ?? null;
                if (!$courseData || $courseData['annual_avg'] < 50) { // Mandatory subjects must pass at 50%
                    $status = 'retained';
                    $courseName = $courseData['course_name'] ?? ('Course ID: ' . $courseId);
                    $ruleViolations[] = "Failed mandatory subject: {$courseName}";
                }
            }
        }

        // 3. Probation Logic
        // If they failed but are close (e.g., within 5%), check probation
        if ($status === 'retained' && $performance['total_avg'] >= ($policy->passing_threshold - 5)) {
            if ($policy->probation_logic === 'promote_with_tag') {
                $status = 'probation';
            }
        }

        return [
            'status' => $status,
            'violations' => $ruleViolations
        ];
    }

    /**
     * Generate draft results for all students in a class section.
     */
    public function generateBatchResults(int $sessionId, int $classId, int $sectionId)
    {
        $policy = PromotionPolicy::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->first();

        if (!$policy) {
            throw new \Exception("No promotion policy defined for this class.");
        }

        $studentIds = DB::table('promotions')
            ->join('users', 'promotions.student_id', '=', 'users.id')
            ->where('promotions.session_id', $sessionId)
            ->where('promotions.class_id', $classId)
            ->where('promotions.section_id', $sectionId)
            ->where('users.status', 'active')
            ->pluck('student_id');

        foreach ($studentIds as $studentId) {
            $performance = $this->calculateStudentPerformance($studentId, $sessionId);
            $evaluation = $this->evaluateStatus($performance, $policy);

            $review = PromotionReview::firstOrNew([
                'student_id' => $studentId,
                'session_id' => $sessionId,
                'class_id' => $classId,
                'section_id' => $sectionId,
            ]);

            // Always update calculated metrics
            $review->calculated_average = $performance['total_avg'];
            $review->calculated_status = $evaluation['status'];

            // Logic for Final Status & Comments
            if (!$review->exists) {
                // New Record: Sync everything
                $review->final_status = $evaluation['status'];
                $review->is_finalized = false;
                $review->is_overridden = false;
                $review->override_comment = !empty($evaluation['violations']) ? implode('; ', $evaluation['violations']) : null;
            } else {
                // Existing Record: Only update final_status if NOT validly overridden/finalized
                if (!$review->is_finalized && !$review->is_overridden) {
                    $review->final_status = $evaluation['status'];
                    // Only update comment if it's currently a system violation message (or empty)
                    // We avoid overwriting manual teacher notes.
                    $review->override_comment = !empty($evaluation['violations']) ? implode('; ', $evaluation['violations']) : null;
                }
            }

            $review->save();
        }
    }
}
