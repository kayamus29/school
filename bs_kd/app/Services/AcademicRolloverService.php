<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\AssignedTeacher;
use App\Models\PromotionPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AcademicRolloverService
{
    public function clonePreviousTermConfiguration(Semester $newSemester): void
    {
        $previousSemester = Semester::query()
            ->where('session_id', $newSemester->session_id)
            ->where('id', '<>', $newSemester->id)
            ->orderByDesc('id')
            ->first();

        if (!$previousSemester) {
            return;
        }

        DB::transaction(function () use ($previousSemester, $newSemester) {
            $courseIdMap = [];

            $previousCourses = Course::query()
                ->where('session_id', $previousSemester->session_id)
                ->where('semester_id', $previousSemester->id)
                ->get();

            foreach ($previousCourses as $course) {
                $newCourse = Course::firstOrCreate(
                    [
                        'course_name' => $course->course_name,
                        'course_type' => $course->course_type,
                        'class_id' => $course->class_id,
                        'semester_id' => $newSemester->id,
                        'session_id' => $newSemester->session_id,
                    ]
                );

                $courseIdMap[$course->id] = $newCourse->id;
            }

            $previousAssignments = AssignedTeacher::query()
                ->where('session_id', $previousSemester->session_id)
                ->where('semester_id', $previousSemester->id)
                ->get();

            foreach ($previousAssignments as $assignment) {
                AssignedTeacher::updateOrCreate(
                    [
                        'teacher_id' => $assignment->teacher_id,
                        'class_id' => $assignment->class_id,
                        'section_id' => $assignment->section_id,
                        'course_id' => $assignment->course_id ? ($courseIdMap[$assignment->course_id] ?? null) : null,
                        'assignment_role' => $assignment->assignment_role,
                        'session_id' => $newSemester->session_id,
                        'semester_id' => $newSemester->id,
                    ],
                    []
                );
            }
        });
    }

    public function rolloverSession(int $sourceSessionId, string $newSessionName): SchoolSession
    {
        return DB::transaction(function () use ($sourceSessionId, $newSessionName) {
            $sourceSession = SchoolSession::findOrFail($sourceSessionId);

            $newSession = SchoolSession::create([
                'session_name' => $newSessionName,
            ]);

            $semesterIdMap = [];
            $classIdMap = [];
            $sectionIdMap = [];
            $courseIdMap = [];

            $sourceSemesters = Semester::where('session_id', $sourceSession->id)->orderBy('id')->get();
            foreach ($sourceSemesters as $semester) {
                $newSemester = Semester::create([
                    'semester_name' => $semester->semester_name,
                    'start_date' => $this->shiftDate($semester->start_date),
                    'end_date' => $this->shiftDate($semester->end_date),
                    'session_id' => $newSession->id,
                    'total_school_days' => $semester->total_school_days,
                ]);

                $semesterIdMap[$semester->id] = $newSemester->id;
            }

            $sourceClasses = SchoolClass::where('session_id', $sourceSession->id)->get();
            foreach ($sourceClasses as $class) {
                $newClass = SchoolClass::create([
                    'class_name' => $class->class_name,
                    'session_id' => $newSession->id,
                    'is_final_grade' => $class->is_final_grade,
                ]);

                $classIdMap[$class->id] = $newClass->id;
            }

            $sourceSections = Section::where('session_id', $sourceSession->id)->get();
            foreach ($sourceSections as $section) {
                $newSection = Section::create([
                    'section_name' => $section->section_name,
                    'room_no' => $section->room_no,
                    'class_id' => $classIdMap[$section->class_id] ?? null,
                    'session_id' => $newSession->id,
                ]);

                $sectionIdMap[$section->id] = $newSection->id;
            }

            $sourceCourses = Course::where('session_id', $sourceSession->id)->get();
            foreach ($sourceCourses as $course) {
                $newCourse = Course::create([
                    'course_name' => $course->course_name,
                    'course_type' => $course->course_type,
                    'class_id' => $classIdMap[$course->class_id] ?? null,
                    'semester_id' => $semesterIdMap[$course->semester_id] ?? null,
                    'session_id' => $newSession->id,
                ]);

                $courseIdMap[$course->id] = $newCourse->id;
            }

            $sourceAssignments = AssignedTeacher::where('session_id', $sourceSession->id)->get();
            foreach ($sourceAssignments as $assignment) {
                AssignedTeacher::create([
                    'teacher_id' => $assignment->teacher_id,
                    'semester_id' => $semesterIdMap[$assignment->semester_id] ?? null,
                    'class_id' => $classIdMap[$assignment->class_id] ?? null,
                    'section_id' => $assignment->section_id ? ($sectionIdMap[$assignment->section_id] ?? null) : null,
                    'course_id' => $assignment->course_id ? ($courseIdMap[$assignment->course_id] ?? null) : null,
                    'assignment_role' => $assignment->assignment_role,
                    'session_id' => $newSession->id,
                ]);
            }

            $sourcePolicies = PromotionPolicy::where('session_id', $sourceSession->id)->get();
            foreach ($sourcePolicies as $policy) {
                $mandatoryCourseIds = collect($policy->mandatory_course_ids ?: [])
                    ->map(fn ($courseId) => $courseIdMap[$courseId] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                PromotionPolicy::create([
                    'class_id' => $classIdMap[$policy->class_id] ?? null,
                    'session_id' => $newSession->id,
                    'calculation_method' => $policy->calculation_method,
                    'passing_threshold' => $policy->passing_threshold,
                    'mandatory_course_ids' => $mandatoryCourseIds,
                    'probation_logic' => $policy->probation_logic,
                ]);
            }

            return $newSession;
        });
    }

    private function shiftDate($date): ?string
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)->addYear()->toDateString();
    }
}
