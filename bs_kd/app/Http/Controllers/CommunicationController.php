<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommunicationRequest;
use App\Http\Requests\CommunicationReplyRequest;
use App\Http\Requests\InboundEmailReplyRequest;
use App\Models\Communication;
use App\Models\CommunicationRecipient;
use App\Models\InboundEmail;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SchoolSession;
use App\Models\Promotion;
use App\Services\CommunicationService;
use App\Services\ImapMailboxService;
use App\Traits\SchoolSession as SchoolSessionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\SchoolSessionInterface;

class CommunicationController extends Controller
{
    use SchoolSessionTrait;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function index(Request $request, CommunicationService $communicationService)
    {
        return $this->renderIndex($request, $communicationService);
    }

    public function preview(CommunicationRequest $request, CommunicationService $communicationService)
    {
        $preview = $communicationService->buildPreview($request->validated(), Auth::user());

        return $this->renderIndex($request, $communicationService, $preview);
    }

    public function send(CommunicationRequest $request, CommunicationService $communicationService)
    {
        $preview = $communicationService->buildPreview($request->validated(), Auth::user());

        if ($preview['qualified_count'] === 0) {
            return redirect()
                ->route('communications.index')
                ->withInput()
                ->withError('No qualified recipients were found for this communication.');
        }

        $communication = $communicationService->send($preview, Auth::user());

        return redirect()
            ->route('communications.show', $communication)
            ->with('success', "Communication sent to {$communication->successful_recipients} recipient(s).");
    }

    public function show(Communication $communication)
    {
        $this->authorizeCommunication($communication);

        $communication->load([
            'creator',
            'session',
            'schoolClass',
            'section',
            'recipients.student.parent_info',
        ]);

        return view('communications.show', compact('communication'));
    }

    public function replyForm(Communication $communication, CommunicationRecipient $recipient)
    {
        abort_unless(Auth::user()->hasRole('Admin'), 403);
        abort_unless((int) $recipient->communication_id === (int) $communication->id, 404);
        abort_unless($communication->channel === 'email', 400, 'Reply is only available for email history.');

        $communication->load(['creator', 'recipients.student.parent_info']);
        $recipient->load('student.parent_info');

        return view('communications.reply', compact('communication', 'recipient'));
    }

    public function replySend(CommunicationReplyRequest $request, Communication $communication, CommunicationRecipient $recipient, CommunicationService $communicationService)
    {
        abort_unless(Auth::user()->hasRole('Admin'), 403);
        abort_unless((int) $recipient->communication_id === (int) $communication->id, 404);
        abort_unless($communication->channel === 'email', 400, 'Reply is only available for email history.');

        $reply = $communicationService->sendReply($communication, $recipient, $request->validated(), Auth::user());

        return redirect()
            ->route('communications.show', $reply)
            ->with('success', 'Reply email sent successfully.');
    }

    public function inbox()
    {
        abort_unless(Auth::user()->hasRole('Admin'), 403);

        $emails = InboundEmail::query()->latest('received_at')->paginate(25);

        return view('communications.inbox', compact('emails'));
    }

    public function syncInbox(ImapMailboxService $imapMailboxService)
    {
        abort_unless(Auth::user()->hasRole('Admin'), 403);

        try {
            $result = $imapMailboxService->sync(Auth::id());

            return redirect()->route('communications.inbox')
                ->with('success', "Inbox synced. {$result['synced']} message(s) processed.");
        } catch (\Throwable $e) {
            return redirect()->route('communications.inbox')
                ->withError($e->getMessage());
        }
    }

    public function showInbound(InboundEmail $inboundEmail)
    {
        abort_unless(Auth::user()->hasRole('Admin'), 403);

        return view('communications.inbox-show', compact('inboundEmail'));
    }

    public function replyInboundForm(InboundEmail $inboundEmail)
    {
        abort_unless(Auth::user()->hasRole('Admin'), 403);

        return view('communications.inbox-reply', compact('inboundEmail'));
    }

    public function replyInboundSend(InboundEmailReplyRequest $request, InboundEmail $inboundEmail, CommunicationService $communicationService)
    {
        abort_unless(Auth::user()->hasRole('Admin'), 403);

        $reply = $communicationService->sendInboundReply($inboundEmail, $request->validated(), Auth::user());

        return redirect()->route('communications.show', $reply)
            ->with('success', 'Inbox reply sent successfully.');
    }

    protected function renderIndex(Request $request, CommunicationService $communicationService, ?array $preview = null)
    {
        $user = Auth::user();
        abort_unless($user->hasAnyRole(['Admin', 'Teacher']), 403);

        $currentSessionId = $this->getSchoolCurrentSession();
        $sessions = SchoolSession::query()->latest()->get();
        $selectedSessionId = (int) old('session_id', $request->input('session_id', $currentSessionId));
        $selectedClassId = old('class_id', $request->input('class_id'));

        $classes = SchoolClass::query()
            ->where('session_id', $selectedSessionId)
            ->orderBy('class_name')
            ->get();

        $sections = Section::query()
            ->where('session_id', $selectedSessionId)
            ->when($selectedClassId, function ($query) use ($selectedClassId) {
                $query->where('class_id', $selectedClassId);
            })
            ->orderBy('section_name')
            ->get();

        $teacherStudents = $user->hasRole('Teacher')
            ? $communicationService->getTeacherStudents($user->id, $selectedSessionId)
            : collect();

        $adminStudents = $user->hasRole('Admin')
            ? Promotion::query()
                ->with(['student', 'schoolClass', 'section'])
                ->where('session_id', $selectedSessionId)
                ->orderBy('class_id')
                ->orderBy('section_id')
                ->get()
            : collect();

        $histories = Communication::query()
            ->with(['creator', 'session', 'schoolClass', 'section'])
            ->when($user->hasRole('Teacher'), function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->latest()
            ->limit(20)
            ->get();

        return view('communications.index', [
            'preview' => $preview,
            'sessions' => $sessions,
            'classes' => $classes,
            'sections' => $sections,
            'teacherStudents' => $teacherStudents,
            'adminStudents' => $adminStudents,
            'histories' => $histories,
            'currentSessionId' => $currentSessionId,
            'formData' => $request->all(),
        ]);
    }

    protected function authorizeCommunication(Communication $communication): void
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return;
        }

        abort_unless($user->hasRole('Teacher') && (int) $communication->created_by === (int) $user->id, 403);
    }
}
