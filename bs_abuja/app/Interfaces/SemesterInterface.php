<?php

namespace App\Interfaces;

interface SemesterInterface {
    public function create($request);

    public function getAll($session_id);

    public function update($id, $new_data);
}
