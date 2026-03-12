<?php

namespace App\Repositories;

use App\Models\SchoolClass;
use App\Interfaces\SchoolClassInterface;
use App\Models\AssignedTeacher;

class SchoolClassRepository implements SchoolClassInterface
{
    public function create($request)
    {
        try {
            SchoolClass::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create School Class. ' . $e->getMessage());
        }
    }

    public function getAllBySession($session_id)
    {
        return SchoolClass::where('session_id', $session_id)->get();
    }

    public function getAllBySessionAndTeacher($session_id, $teacher_id)
    {
        return AssignedTeacher::with('schoolClass')->where('teacher_id', $teacher_id)
            ->where('session_id', $session_id)
            ->get();
    }

    public function getAllWithCoursesBySession($session_id)
    {
        return SchoolClass::with(['courses', 'syllabi', 'assignedTeachers.teacher'])->where('session_id', $session_id)->get();
    }

    public function getClassesAndSections($session_id, $teacher_id = null)
    {
        $school_classes = $this->getAllWithCoursesBySession($session_id);

        $sectionRepository = new SectionRepository();
        $school_sections = $sectionRepository->getAllBySession($session_id);

        if ($teacher_id) {
            // Get all unique class and section IDs the teacher is assigned to
            $assignments = AssignedTeacher::where('teacher_id', $teacher_id)
                ->where('session_id', $session_id)
                ->get();

            $assignedClassIds = $assignments->pluck('class_id')->unique()->toArray();
            $assignedSectionIds = $assignments->pluck('section_id')->unique()->toArray();

            // Filter classes
            $school_classes = $school_classes->filter(function ($class) use ($assignedClassIds) {
                return in_array($class->id, $assignedClassIds);
            });

            // Filter sections
            $school_sections = $school_sections->filter(function ($section) use ($assignedSectionIds) {
                return in_array($section->id, $assignedSectionIds);
            });
        }

        $data = [
            'school_classes' => $school_classes,
            'school_sections' => $school_sections,
        ];

        return $data;
    }

    public function findById($class_id)
    {
        return SchoolClass::find($class_id);
    }

    public function update($request)
    {
        try {
            SchoolClass::find($request->class_id)->update([
                'class_name' => $request->class_name,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update School Class. ' . $e->getMessage());
        }
    }

    public function updateFinalGradeDesignations(array $finalGradeClassIds, int $sessionId)
    {
        try {
            // Reset all for current session
            SchoolClass::where('session_id', $sessionId)->update(['is_final_grade' => false]);

            // Set selected
            if (!empty($finalGradeClassIds)) {
                SchoolClass::whereIn('id', $finalGradeClassIds)->update(['is_final_grade' => true]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update final grade designations. ' . $e->getMessage());
        }
    }
}