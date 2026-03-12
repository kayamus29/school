@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-newspaper"></i> End of Term Update</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">End of Term Update</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <form action="{{ route('end-term-updates.edit') }}" method="GET" class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Semester</label>
                                    <select name="semester_id" class="form-select bg-light border-light" onchange="this.form.submit()">
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ (int) $selectedSemesterId === (int) $semester->id ? 'selected' : '' }}>
                                                {{ $semester->semester_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-7 text-muted small">
                                    Select the term you want to publish the newsletter and resumption details for.
                                </div>
                            </form>
                        </div>
                    </div>

                    @if(empty($hasEndTermUpdatesTable))
                        <div class="alert alert-warning border-0 shadow-sm">
                            Run the pending migrations first from <code>/deploy/migrate</code> before using this page.
                        </div>
                    @elseif($selectedSemester)
                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3">
                                        <h6 class="mb-0 fw-semibold text-dark">{{ $selectedSemester->semester_name }} Content</h6>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('end-term-updates.store') }}" method="POST" id="end-term-update-form">
                                            @csrf
                                            <input type="hidden" name="semester_id" value="{{ $selectedSemester->id }}">

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Headline</label>
                                                <input
                                                    type="text"
                                                    name="title"
                                                    id="title"
                                                    class="form-control bg-light border-light"
                                                    value="{{ old('title', $update->title) }}"
                                                    placeholder="School News, Upcoming Events & Holiday Wishes">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold d-block">Content Format</label>
                                                @php $contentFormat = old('content_format', $update->content_format ?? 'plain_text'); @endphp
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="content_format" id="format_plain" value="plain_text" {{ $contentFormat === 'plain_text' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="format_plain">Plain Text</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="content_format" id="format_html" value="html" {{ $contentFormat === 'html' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="format_html">HTML</label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Newsletter Content</label>
                                                <textarea
                                                    name="content_body"
                                                    id="content_body"
                                                    rows="12"
                                                    class="form-control bg-light border-light"
                                                    placeholder="Type your end-of-term message here...">{{ old('content_body', $update->content_body) }}</textarea>
                                                <div class="form-text">
                                                    Plain text preserves line breaks. HTML mode accepts formatted markup and will be sanitized before display on the report card.
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Newsletter Link</label>
                                                <input
                                                    type="url"
                                                    name="newsletter_url"
                                                    id="newsletter_url"
                                                    class="form-control bg-light border-light"
                                                    value="{{ old('newsletter_url', $update->newsletter_url) }}"
                                                    placeholder="https://example.com/end-of-term-newsletter">
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Next Term Label</label>
                                                    <input
                                                        type="text"
                                                        name="next_term_label"
                                                        id="next_term_label"
                                                        class="form-control bg-light border-light"
                                                        value="{{ old('next_term_label', $update->next_term_label ?: 'Next Term') }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Resumption Date</label>
                                                    <input
                                                        type="date"
                                                        name="next_resumption_date"
                                                        id="next_resumption_date"
                                                        class="form-control bg-light border-light"
                                                        value="{{ old('next_resumption_date', optional($update->next_resumption_date)->format('Y-m-d')) }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Fees Deadline</label>
                                                    <input
                                                        type="date"
                                                        name="fee_deadline"
                                                        id="fee_deadline"
                                                        class="form-control bg-light border-light"
                                                        value="{{ old('fee_deadline', optional($update->fee_deadline)->format('Y-m-d')) }}">
                                                </div>
                                            </div>

                                            <div class="mt-3 mb-4">
                                                <label class="form-label fw-semibold">Resumption Note / Other Details</label>
                                                <textarea
                                                    name="resumption_note"
                                                    id="resumption_note"
                                                    rows="4"
                                                    class="form-control bg-light border-light"
                                                    placeholder="Add any extra instructions for parents and students...">{{ old('resumption_note', $update->resumption_note) }}</textarea>
                                            </div>

                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-outline-secondary" id="preview-button">
                                                    <i class="bi bi-eye me-1"></i> Preview
                                                </button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-save me-1"></i> Save Update
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold text-dark">Preview</h6>
                                        <span class="badge bg-light text-dark border">{{ $selectedSemester->semester_name }}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="border rounded-3 p-3 bg-light-subtle" id="preview-surface" style="min-height: 520px;">
                                            <div class="small text-muted">Click preview to see how the report card section will look.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning border-0 shadow-sm">
                            Create a semester first before publishing an end-of-term update.
                        </div>
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

@if($selectedSemester)
    <script>
        (function () {
            const previewButton = document.getElementById('preview-button');
            const surface = document.getElementById('preview-surface');
            const titleInput = document.getElementById('title');
            const contentInput = document.getElementById('content_body');
            const linkInput = document.getElementById('newsletter_url');
            const nextTermLabelInput = document.getElementById('next_term_label');
            const resumptionDateInput = document.getElementById('next_resumption_date');
            const feeDeadlineInput = document.getElementById('fee_deadline');
            const resumptionNoteInput = document.getElementById('resumption_note');

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.textContent = value || '';
                return div.innerHTML;
            }

            function formatDate(value) {
                if (!value) return 'Not set';
                const date = new Date(value + 'T00:00:00');
                return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString(undefined, {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function renderPreview() {
                const formatInput = document.querySelector('input[name="content_format"]:checked');
                const format = formatInput ? formatInput.value : 'plain_text';
                const title = titleInput.value.trim() || 'End of Term Update';
                const content = contentInput.value || '';
                const link = linkInput.value.trim();
                const nextTermLabel = nextTermLabelInput.value.trim() || 'Next Term';
                const resumptionDate = formatDate(resumptionDateInput.value);
                const feeDeadline = formatDate(feeDeadlineInput.value);
                const note = resumptionNoteInput.value.trim();

                const contentHtml = format === 'html'
                    ? content
                    : '<div style="white-space: pre-wrap; line-height: 1.8;">' + escapeHtml(content) + '</div>';

                surface.innerHTML = `
                    <div style="display:flex; gap:12px; align-items:center; margin-bottom:16px;">
                        <div style="width:42px; height:42px; border-radius:50%; background:#0d2545; color:#f0d080; display:flex; align-items:center; justify-content:center; font-weight:700;">N</div>
                        <div>
                            <div style="font-size:10px; letter-spacing:2px; text-transform:uppercase; color:#c8962e; font-weight:700;">End of Term</div>
                            <div style="font-family:'Playfair Display', serif; font-size:18px; color:#0d2545; font-weight:700;">${escapeHtml(title)}</div>
                        </div>
                    </div>
                    <div style="background:#fff; border:1px solid #d6d0c4; border-radius:10px; padding:16px; color:#0d2545;">
                        ${contentHtml || '<div style="color:#6b6453;">No newsletter content yet.</div>'}
                    </div>
                    ${link ? `<div style="margin-top:14px;"><a href="${escapeHtml(link)}" target="_blank" rel="noopener" style="color:#0d2545; font-weight:700;">Open newsletter link</a></div>` : ''}
                    <div style="margin-top:18px; padding:14px 16px; border-radius:10px; background:#faf8f3; border:1px solid #d6d0c4;">
                        <div style="font-size:10px; letter-spacing:2px; text-transform:uppercase; color:#c8962e; font-weight:700; margin-bottom:6px;">${escapeHtml(nextTermLabel)}</div>
                        <div style="font-size:14px; font-weight:600; color:#0d2545;">Resumption date: ${escapeHtml(resumptionDate)}</div>
                        <div style="font-size:12px; color:#6b6453; margin-top:4px;">Fees deadline: ${escapeHtml(feeDeadline)}</div>
                        ${note ? `<div style="font-size:12px; color:#6b6453; margin-top:10px; white-space:pre-wrap;">${escapeHtml(note)}</div>` : ''}
                    </div>
                `;
            }

            previewButton.addEventListener('click', renderPreview);
            renderPreview();
        })();
    </script>
@endif
@endsection
