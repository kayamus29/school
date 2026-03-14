@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-envelope-paper"></i> Communication</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Communication</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">
                                        {{ Auth::user()->hasRole('Admin') ? 'Bulk Guardian Communication' : 'Single Guardian Communication' }}
                                    </h5>
                                    <form method="POST" action="{{ route('communications.preview') }}">
                                        @csrf

                                        @if(Auth::user()->hasRole('Admin'))
                                            <div class="mb-3">
                                                <label class="form-label" for="scope">Mode</label>
                                                <select class="form-select" id="scope" name="scope" required>
                                                    <option value="bulk" {{ old('scope', $formData['scope'] ?? 'bulk') === 'bulk' ? 'selected' : '' }}>Bulk</option>
                                                    <option value="single" {{ old('scope', $formData['scope'] ?? null) === 'single' ? 'selected' : '' }}>Single Student</option>
                                                </select>
                                            </div>
                                        @else
                                            <input type="hidden" name="scope" value="single">
                                        @endif

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="channel">Channel</label>
                                                <select class="form-select" id="channel" name="channel" required>
                                                    <option value="email" {{ old('channel', $formData['channel'] ?? 'email') === 'email' ? 'selected' : '' }}>{{ Auth::user()->hasRole('Admin') ? 'Bulk Email' : 'Single Email' }}</option>
                                                    <option value="sms" {{ old('channel', $formData['channel'] ?? null) === 'sms' ? 'selected' : '' }}>{{ Auth::user()->hasRole('Admin') ? 'Bulk SMS' : 'Single SMS' }}</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label" for="session_id">Academic Session</label>
                                                <select class="form-select" id="session_id" name="session_id" required>
                                                    @foreach($sessions as $session)
                                                        <option value="{{ $session->id }}" {{ (string) old('session_id', $formData['session_id'] ?? $currentSessionId) === (string) $session->id ? 'selected' : '' }}>
                                                            {{ $session->session_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        @if(Auth::user()->hasRole('Admin'))
                                            <div class="row bulk-scope">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label" for="class_id">Class</label>
                                                    <select class="form-select" id="class_id" name="class_id">
                                                        <option value="">All classes</option>
                                                        @foreach($classes as $class)
                                                            <option value="{{ $class->id }}" {{ (string) old('class_id', $formData['class_id'] ?? null) === (string) $class->id ? 'selected' : '' }}>
                                                                {{ $class->class_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label" for="section_id">Section</label>
                                                    <select class="form-select" id="section_id" name="section_id">
                                                        <option value="">All sections</option>
                                                        @foreach($sections as $section)
                                                            <option value="{{ $section->id }}" {{ (string) old('section_id', $formData['section_id'] ?? null) === (string) $section->id ? 'selected' : '' }}>
                                                                {{ $section->section_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3 single-scope d-none">
                                                <label class="form-label" for="student_id">Student</label>
                                                <select class="form-select" id="student_id" name="student_id">
                                                    <option value="">Select a student</option>
                                                    @foreach($adminStudents as $promotion)
                                                        <option value="{{ $promotion->student_id }}" {{ (string) old('student_id', $formData['student_id'] ?? null) === (string) $promotion->student_id ? 'selected' : '' }}>
                                                            {{ optional($promotion->student)->first_name }} {{ optional($promotion->student)->last_name }}
                                                            - {{ optional($promotion->schoolClass)->class_name }}
                                                            / {{ optional($promotion->section)->section_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <label class="form-label" for="student_id">Student</label>
                                                <select class="form-select" id="student_id" name="student_id" required>
                                                    <option value="">Select a student</option>
                                                    @foreach($teacherStudents as $promotion)
                                                        <option value="{{ $promotion->student_id }}" {{ (string) old('student_id', $formData['student_id'] ?? null) === (string) $promotion->student_id ? 'selected' : '' }}>
                                                            {{ optional($promotion->student)->first_name }} {{ optional($promotion->student)->last_name }}
                                                            - {{ optional($promotion->schoolClass)->class_name }}
                                                            / {{ optional($promotion->section)->section_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        <div class="mb-3 email-only">
                                            <label class="form-label" for="subject">Email Subject</label>
                                            <input type="text" class="form-control" id="subject" name="subject"
                                                value="{{ old('subject', $formData['subject'] ?? null) }}" placeholder="Subject for guardian email">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="message-editor">Message Draft</label>
                                            <textarea class="form-control" id="message-editor" name="message" rows="10"
                                                placeholder="{{ Auth::user()->hasRole('Admin') ? 'Draft the bulk email or SMS to guardians...' : 'Draft the message to this guardian...' }}">{{ old('message', $formData['message'] ?? null) }}</textarea>
                                            <div class="form-text sms-only d-none">Guardian phone numbers are validated and normalized before SMS is sent.</div>
                                            <div class="form-text email-only">HTML email is supported. Invalid guardian emails are skipped from the qualified list.</div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search"></i> Preview Recipients
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @if($preview)
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">Preview</h5>
                                                <p class="text-muted mb-0">
                                                    {{ $preview['qualified_count'] }} qualified, {{ $preview['invalid_count'] }} skipped
                                                </p>
                                            </div>
                                            <form method="POST" action="{{ route('communications.send') }}">
                                                @csrf
                                                <input type="hidden" name="scope" value="{{ old('scope', $formData['scope'] ?? (Auth::user()->hasRole('Admin') ? 'bulk' : 'single')) }}">
                                                <input type="hidden" name="channel" value="{{ old('channel', $formData['channel'] ?? 'email') }}">
                                                <input type="hidden" name="session_id" value="{{ old('session_id', $formData['session_id'] ?? $currentSessionId) }}">
                                                <input type="hidden" name="class_id" value="{{ old('class_id', $formData['class_id'] ?? null) }}">
                                                <input type="hidden" name="section_id" value="{{ old('section_id', $formData['section_id'] ?? null) }}">
                                                <input type="hidden" name="student_id" value="{{ old('student_id', $formData['student_id'] ?? null) }}">
                                                <input type="hidden" name="subject" value="{{ old('subject', $formData['subject'] ?? null) }}">
                                                <textarea name="message" class="d-none">{{ old('message', $formData['message'] ?? null) }}</textarea>
                                                <button type="submit" class="btn btn-success" {{ $preview['qualified_count'] === 0 ? 'disabled' : '' }}>
                                                    <i class="bi bi-send"></i> Send Now
                                                </button>
                                            </form>
                                        </div>

                                        @if($preview['channel'] === 'email')
                                            <div class="border rounded p-3 mb-4 bg-light">
                                                <div class="small text-muted mb-2">Email preview</div>
                                                <h6>{{ $preview['subject'] }}</h6>
                                                <div>{!! $preview['message_html'] !!}</div>
                                            </div>
                                        @else
                                            <div class="border rounded p-3 mb-4 bg-light">
                                                <div class="small text-muted mb-2">SMS preview</div>
                                                <div style="white-space: pre-wrap;">{{ $preview['message_text'] }}</div>
                                            </div>
                                        @endif

                                        <h6>Qualified Recipients</h6>
                                        <div class="table-responsive mb-4">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Class</th>
                                                        <th>Section</th>
                                                        <th>{{ $preview['channel'] === 'email' ? 'Guardian Email' : 'Guardian Phone' }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($preview['qualified_recipients'] as $recipient)
                                                        <tr>
                                                            <td>{{ $recipient['student_name'] }}</td>
                                                            <td>{{ $recipient['class_name'] }}</td>
                                                            <td>{{ $recipient['section_name'] }}</td>
                                                            <td>{{ $recipient['destination'] }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-muted">No qualified recipients matched this draft.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <h6>Skipped Recipients</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Class</th>
                                                        <th>Section</th>
                                                        <th>Current Value</th>
                                                        <th>Reason</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($preview['invalid_recipients'] as $recipient)
                                                        <tr>
                                                            <td>{{ $recipient['student_name'] }}</td>
                                                            <td>{{ $recipient['class_name'] }}</td>
                                                            <td>{{ $recipient['section_name'] }}</td>
                                                            <td>{{ $recipient['destination'] ?? 'N/A' }}</td>
                                                            <td class="text-danger">{{ $recipient['reason'] }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-muted">All matched recipients are valid.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-lg-5">
                            @if(Auth::user()->hasRole('Admin'))
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Inbox</h5>
                                        <p class="text-muted">Sync incoming mailbox emails and reply from the communication module.</p>
                                        <a href="{{ route('communications.inbox') }}" class="btn btn-outline-primary">Open Inbox</a>
                                    </div>
                                </div>
                            @endif
                            <div class="card shadow-sm border-0">
                                <div class="card-body">
                                    <h5 class="card-title">History</h5>
                                    @forelse($histories as $history)
                                        <div class="border rounded p-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="small text-muted text-uppercase mb-1">{{ $history->channel }}</div>
                                                    <h6 class="mb-1">
                                                        {{ $history->subject ?: \Illuminate\Support\Str::limit($history->message, 60) }}
                                                    </h6>
                                                    <div class="small text-muted">
                                                        {{ optional($history->created_at)->format('d M Y, h:i A') }}
                                                        @if($history->session)
                                                            | {{ $history->session->session_name }}
                                                        @endif
                                                    </div>
                                                    <div class="small mt-1">
                                                        {{ $history->successful_recipients }}/{{ $history->total_recipients }} sent
                                                        @if($history->failed_recipients > 0)
                                                            <span class="text-danger">, {{ $history->failed_recipients }} failed</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="badge {{ $history->status === 'completed' ? 'bg-success' : ($history->status === 'partial' ? 'bg-warning text-dark' : ($history->status === 'failed' ? 'bg-danger' : 'bg-secondary')) }}">
                                                    {{ ucfirst($history->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('communications.show', $history) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-0">No communication history yet.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/30.0.0/classic/ckeditor.js"></script>
<script>
    const channelSelect = document.getElementById('channel');
    const scopeSelect = document.getElementById('scope');
    const emailOnlyBlocks = document.querySelectorAll('.email-only');
    const smsOnlyBlocks = document.querySelectorAll('.sms-only');
    const bulkScopeBlocks = document.querySelectorAll('.bulk-scope');
    const singleScopeBlocks = document.querySelectorAll('.single-scope');

    function toggleCommunicationFields() {
        const isEmail = channelSelect?.value === 'email';

        emailOnlyBlocks.forEach((element) => {
            element.classList.toggle('d-none', !isEmail);
        });

        smsOnlyBlocks.forEach((element) => {
            element.classList.toggle('d-none', isEmail);
        });
    }

    function toggleScopeFields() {
        const scope = scopeSelect?.value ?? 'bulk';
        const isSingle = scope === 'single';

        bulkScopeBlocks.forEach((element) => {
            element.classList.toggle('d-none', isSingle);
        });

        singleScopeBlocks.forEach((element) => {
            element.classList.toggle('d-none', !isSingle);
        });
    }

    if (channelSelect) {
        channelSelect.addEventListener('change', toggleCommunicationFields);
        toggleCommunicationFields();
    }

    if (scopeSelect) {
        scopeSelect.addEventListener('change', toggleScopeFields);
        toggleScopeFields();
    }

    ClassicEditor.create(document.querySelector('#message-editor'), {
        toolbar: ['heading', 'bold', 'italic', '|', 'link', 'insertTable', 'numberedList', 'bulletedList', '|', 'undo', 'redo']
    }).catch((error) => {
        console.error(error);
    });
</script>
@endsection
