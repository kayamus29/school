<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\UserInterface;
use App\Http\Requests\SchoolClassStoreRequest;
use App\Traits\SchoolSession;

class SchoolClassController extends Controller
{
    use SchoolSession;
    protected $schoolClassRepository;
    protected $schoolSessionRepository;
    protected $userRepository;

    /**
     * Create a new Controller instance
     * 
     * @param SchoolClassInterface $schoolClassRepository
     * @return void
     */
    public function __construct(SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository, UserInterface $userRepository)
    {
        $this->middleware(['can:view classes']);

        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $data = $this->schoolClassRepository->getClassesAndSections($current_school_session_id);
        $data['teachers'] = $this->userRepository->getAllTeachers();

        return view('classes.index', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SchoolClassStoreRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(SchoolClassStoreRequest $request)
    {
        try {
            $this->schoolClassRepository->create($request->validated());

            return back()->with('status', 'Class creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $class_id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($class_id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $schoolClass = $this->schoolClassRepository->findById($class_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'class_id' => $class_id,
            'schoolClass' => $schoolClass,
        ];
        return view('classes.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            $this->schoolClassRepository->update($request);

            return back()->with('status', 'Class edit was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
