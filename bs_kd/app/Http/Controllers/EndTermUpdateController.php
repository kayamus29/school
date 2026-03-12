<?php

namespace App\Http\Controllers;

use App\Http\Requests\EndTermUpdateStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Models\EndTermUpdate;
use App\Models\Semester;
use App\Traits\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EndTermUpdateController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware(['auth']);
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function edit(Request $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Only Admins can manage end of term updates.');
        }

        $sessionId = $this->getSchoolCurrentSession();
        $semesters = Semester::where('session_id', $sessionId)->orderBy('id')->get();
        $selectedSemesterId = (int) ($request->query('semester_id') ?: optional($semesters->first())->id);
        $hasEndTermUpdatesTable = Schema::hasTable('end_term_updates');

        $selectedSemester = $semesters->firstWhere('id', $selectedSemesterId);
        if (!$selectedSemester && $semesters->isNotEmpty()) {
            $selectedSemester = $semesters->first();
            $selectedSemesterId = (int) $selectedSemester->id;
        }

        $update = null;
        if ($hasEndTermUpdatesTable && $selectedSemesterId) {
            $update = EndTermUpdate::firstOrNew([
                'session_id' => $sessionId,
                'semester_id' => $selectedSemesterId,
            ], [
                'content_format' => 'plain_text',
                'next_term_label' => 'Next Term',
            ]);
        }

        return view('end-term-updates.edit', compact('semesters', 'selectedSemester', 'selectedSemesterId', 'update', 'hasEndTermUpdatesTable'));
    }

    public function store(EndTermUpdateStoreRequest $request)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'Only Admins can manage end of term updates.');
        }

        if (!Schema::hasTable('end_term_updates')) {
            return back()->with('error', 'Run migrations first from /deploy/migrate before saving end of term updates.');
        }

        $sessionId = $this->getSchoolCurrentSession();
        $semester = Semester::where('session_id', $sessionId)
            ->where('id', $request->integer('semester_id'))
            ->firstOrFail();

        EndTermUpdate::updateOrCreate(
            [
                'session_id' => $sessionId,
                'semester_id' => $semester->id,
            ],
            [
                'title' => $request->input('title'),
                'content_format' => $request->input('content_format', 'plain_text'),
                'content_body' => $request->input('content_body'),
                'newsletter_url' => $request->input('newsletter_url'),
                'next_term_label' => $request->input('next_term_label'),
                'next_resumption_date' => $request->input('next_resumption_date'),
                'fee_deadline' => $request->input('fee_deadline'),
                'resumption_note' => $request->input('resumption_note'),
                'published_by' => Auth::id(),
            ]
        );

        return redirect()
            ->route('end-term-updates.edit', ['semester_id' => $semester->id])
            ->with('status', 'End of term update saved successfully.');
    }
}
