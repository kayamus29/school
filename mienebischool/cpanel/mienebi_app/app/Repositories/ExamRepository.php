<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Models\Semester;
use App\Models\SchoolClass;
use App\Interfaces\ExamInterface;

class ExamRepository implements ExamInterface
{
    public function create($request)
    {
        try {
            Exam::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create exam. ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            Exam::destroy($id);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete exam. ' . $e->getMessage());
        }
    }

    public function getAll($session_id, $semester_id, $class_id)
    {
        if ($semester_id == 0 || $class_id == 0) {
            $semester = Semester::where('session_id', $session_id)->first();
            $schoolClass = SchoolClass::where('session_id', $session_id)->first();

            if (!$semester || !$schoolClass) {
                return collect([]);
            }

            $semester_id = $semester->id;
            $class_id = $schoolClass->id;
        }
        return Exam::with(['course', 'examRule'])->where('session_id', $session_id)
            ->where('semester_id', $semester_id)
            ->where('class_id', $class_id)
            ->get();
    }

    public function ensureExamsExistForClass($session_id, $semester_id, $class_id)
    {
        $gradingSystemRepository = new GradingSystemRepository();
        $gradingSystem = $gradingSystemRepository->getGradingSystem($session_id, $semester_id, $class_id);

        if (!$gradingSystem) {
            return;
        }

        $courseRepository = app(\App\Interfaces\CourseInterface::class);
        $courses = $courseRepository->getByClassId($class_id);

        $academicSetting = \App\Models\AcademicSetting::find(1);

        foreach ($courses as $course) {
            $exam = Exam::where('session_id', $session_id)
                ->where('semester_id', $semester_id)
                ->where('class_id', $class_id)
                ->where('course_id', $course->id)
                ->first();

            if (!$exam) {
                $exam = Exam::create([
                    'exam_name' => $course->course_name . ' Exam',
                    'start_date' => now(),
                    'end_date' => now()->addDays(7),
                    'semester_id' => $semester_id,
                    'class_id' => $class_id,
                    'course_id' => $course->id,
                    'session_id' => $session_id
                ]);

                \App\Models\ExamRule::create([
                    'total_marks' => 100,
                    'pass_marks' => 45,
                    'marks_breakdown' => $academicSetting->marks_breakdown ?? [
                        ['name' => 'Final Exam', 'weight' => 70],
                        ['name' => 'CA 1', 'weight' => 15],
                        ['name' => 'CA 2', 'weight' => 15]
                    ],
                    'marks_distribution_note' => 'Auto-generated',
                    'exam_id' => $exam->id,
                    'session_id' => $session_id
                ]);
            }
        }
    }
}