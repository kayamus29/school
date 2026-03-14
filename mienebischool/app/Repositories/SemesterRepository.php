<?php

namespace App\Repositories;

use App\Interfaces\SemesterInterface;
use App\Models\Semester;
use App\Models\SchoolSession;

class SemesterRepository implements SemesterInterface {
    public function create($request) {
        try {
            return Semester::create($request);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create School Semester. '.$e->getMessage());
        }
    }

    public function getAll($session_id)
    {
        return Semester::where('session_id', $session_id)->orderBy('id', 'desc')->get();
    }

    public function update($id, $new_data)
    {
        try {
            $semester = Semester::findOrFail($id);
            $semester->update($new_data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Semester. ' . $e->getMessage());
        }
    }
}
