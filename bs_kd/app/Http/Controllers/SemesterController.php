<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Interfaces\SemesterInterface;
use App\Http\Requests\SemesterStoreRequest;
use App\Services\AcademicRolloverService;

class SemesterController extends Controller
{
    protected $semesterRepository;
    protected $academicRolloverService;

    public function __construct(SemesterInterface $semesterRepository, AcademicRolloverService $academicRolloverService) {
        $this->semesterRepository = $semesterRepository;
        $this->academicRolloverService = $academicRolloverService;
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  SemesterStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SemesterStoreRequest $request)
    {
        try {
            $semester = $this->semesterRepository->create($request->validated());
            if ($semester) {
                $this->academicRolloverService->clonePreviousTermConfiguration($semester);
            }

            return back()->with('status', 'Semester creation was successful and previous term setup was copied forward.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
