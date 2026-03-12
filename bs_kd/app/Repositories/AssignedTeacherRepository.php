<?php

namespace App\Repositories;

use App\Models\Semester;
use App\Models\AssignedTeacher;
use App\Interfaces\AssignedTeacherInterface;

class AssignedTeacherRepository implements AssignedTeacherInterface
{

    public function assign($request)
    {
        try {
            $role = $request['assignment_role'] ?? ($request['course_id'] ? AssignedTeacher::ROLE_SUBJECT_TEACHER : AssignedTeacher::ROLE_SECTION_TEACHER);
            $courseId = $role === AssignedTeacher::ROLE_SUBJECT_TEACHER ? ($request['course_id'] ?: null) : null;

            if ($role === AssignedTeacher::ROLE_CLASS_SUPERVISOR) {
                AssignedTeacher::updateOrCreate(
                    [
                        'class_id' => $request['class_id'],
                        'section_id' => $request['section_id'],
                        'course_id' => null,
                        'assignment_role' => AssignedTeacher::ROLE_CLASS_SUPERVISOR,
                        'session_id' => $request['session_id'],
                        'semester_id' => $request['semester_id'],
                    ],
                    [
                        'teacher_id' => $request['teacher_id'],
                    ]
                );

                return;
            }

            if ($role === AssignedTeacher::ROLE_SECTION_TEACHER) {
                AssignedTeacher::firstOrCreate(
                    [
                        'teacher_id' => $request['teacher_id'],
                        'class_id' => $request['class_id'],
                        'section_id' => $request['section_id'],
                        'course_id' => null,
                        'assignment_role' => AssignedTeacher::ROLE_SECTION_TEACHER,
                        'session_id' => $request['session_id'],
                        'semester_id' => $request['semester_id'],
                    ]
                );

                return;
            }

            AssignedTeacher::updateOrCreate(
                [
                    'class_id' => $request['class_id'],
                    'section_id' => $request['section_id'],
                    'course_id' => $courseId,
                    'assignment_role' => AssignedTeacher::ROLE_SUBJECT_TEACHER,
                    'session_id' => $request['session_id'],
                    'semester_id' => $request['semester_id'],
                ],
                [
                    'teacher_id' => $request['teacher_id']
                ]
            );
        } catch (\Exception $e) {
            throw new \Exception('Failed to assign teacher. ' . $e->getMessage());
        }
    }

    public function getTeacherCourses($session_id, $teacher_id, $semester_id)
    {
        if ($semester_id == 0) {
            $semester_id = Semester::where('session_id', $session_id)
                ->first()->id;
        }
        return AssignedTeacher::with(['course', 'schoolClass', 'section'])->where('session_id', $session_id)
            ->where('teacher_id', $teacher_id)
            ->where('semester_id', $semester_id)
            ->get();
    }

    public function getAssignedTeacher($session_id, $semester_id, $class_id, $section_id, $course_id)
    {
        if ($semester_id == 0) {
            $semester_id = Semester::where('session_id', $session_id)
                ->first()->id;
        }
        $query = AssignedTeacher::where('session_id', $session_id)
            ->where('semester_id', $semester_id)
            ->where('class_id', $class_id);

        if ($section_id) {
            $query->where('section_id', $section_id);
        } else {
            $query->whereNull('section_id');
        }

        if ($course_id) {
            $query->where('course_id', $course_id);
        } else {
            $query->sectionTeachers();
        }

        return $query->first();
    }
}
