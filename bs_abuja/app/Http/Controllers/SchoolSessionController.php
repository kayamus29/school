<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\SchoolSessionInterface;
use App\Http\Requests\SchoolSessionStoreRequest;
use App\Http\Requests\SchoolSessionBrowseRequest;
use App\Services\AcademicRolloverService;
use Illuminate\Http\Request;

class SchoolSessionController extends Controller
{
    protected $schoolSessionRepository;
    protected $academicRolloverService;

    /**
    * Create a new Controller instance
    * 
    * @param SchoolSessionInterface $schoolSessionRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository, AcademicRolloverService $academicRolloverService) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->academicRolloverService = $academicRolloverService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SchoolSessionStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SchoolSessionStoreRequest $request)
    {
        try {
            $this->schoolSessionRepository->create($request->validated());

            return back()->with('status', 'Session creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
        
    }

    /**
     * Save the selected school session as current session for
     * browsing.
     *
     * @param  SchoolSessionBrowseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function browse(SchoolSessionBrowseRequest $request)
    {
        try {
            $this->schoolSessionRepository->browse($request->validated());

            return back()->with('status', 'Browsing session set was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function rollover(Request $request)
    {
        if (!auth()->user()->can('create school sessions')) {
            abort(403);
        }

        $request->validate([
            'source_session_id' => 'required|exists:school_sessions,id',
            'session_name' => 'required|string|max:255|unique:school_sessions,session_name',
        ]);

        try {
            $newSession = $this->academicRolloverService->rolloverSession((int) $request->source_session_id, (string) $request->session_name);

            return back()->with('status', 'Session rollover completed successfully for ' . $newSession->session_name . '.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
