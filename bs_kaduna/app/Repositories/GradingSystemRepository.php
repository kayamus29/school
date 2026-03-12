<?php

namespace App\Repositories;

use App\Models\GradingSystem;
use Illuminate\Support\Facades\DB;

class GradingSystemRepository
{
    public function store($request)
    {
        DB::beginTransaction();
        try {
            $gradingSystem = GradingSystem::create($request);

            if (isset($request['class_ids']) && is_array($request['class_ids'])) {
                $gradingSystem->schoolClasses()->sync($request['class_ids']);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create grading system. ' . $e->getMessage());
        }
    }

    public function getAll($session_id)
    {
        return GradingSystem::with(['semester', 'schoolClass', 'schoolClasses'])
            ->where('session_id', $session_id)
            ->get();
    }

    public function getGradingSystem($session_id, $semester_id, $class_id)
    {
        return GradingSystem::with(['semester', 'schoolClass', 'schoolClasses'])
            ->where('session_id', $session_id)
            ->where('semester_id', $semester_id)
            ->where(function ($query) use ($class_id) {
                $query->where('class_id', $class_id)
                    ->orWhereHas('schoolClasses', function ($q) use ($class_id) {
                        $q->where('school_classes.id', $class_id);
                    });
            })
            ->first();
    }
}