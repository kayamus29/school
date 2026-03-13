@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-3">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-newspaper"></i> End of Term News</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('results.student', ['session_id' => $session->id, 'semester_id' => $semester->id]) }}">Results</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $semester->semester_name }}</li>
                            </ol>
                        </nav>

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <div class="small text-uppercase text-muted fw-bold">Session</div>
                                    <div class="fw-semibold">{{ $session->session_name }}</div>
                                </div>
                                <div>
                                    <div class="small text-uppercase text-muted fw-bold">Term</div>
                                    <div class="fw-semibold">{{ $semester->semester_name }}</div>
                                </div>
                                <div>
                                    <a href="{{ route('results.student', ['session_id' => $session->id, 'semester_id' => $semester->id]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-left me-1"></i> Back To Result
                                    </a>
                                </div>
                            </div>
                        </div>

                        @if($endTermUpdate)
                            @php
                                $renderedNewsletter = '';
                                if (!empty($endTermUpdate->content_body)) {
                                    $renderedNewsletter = $endTermUpdate->content_format === 'html'
                                        ? Purify::clean($endTermUpdate->content_body)
                                        : nl2br(e($endTermUpdate->content_body));
                                }
                            @endphp

                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body p-4">
                                    <div class="small text-uppercase text-warning fw-bold mb-2">News & Events</div>
                                    <h3 class="mb-3">{{ $endTermUpdate->title ?: ($semester->semester_name . ' Update') }}</h3>

                                    @if($renderedNewsletter)
                                        <div class="text-muted" style="line-height: 1.9;">{!! $renderedNewsletter !!}</div>
                                    @else
                                        <p class="text-muted mb-0">No news content has been published for this term yet.</p>
                                    @endif
                                </div>
                            </div>

                            @if(!empty($endTermUpdate->next_resumption_date) || !empty($endTermUpdate->fee_deadline) || !empty($endTermUpdate->resumption_note))
                                <div class="card shadow-sm border-0">
                                    <div class="card-body p-4">
                                        <div class="small text-uppercase text-primary fw-bold mb-2">{{ $endTermUpdate->next_term_label ?: 'Next Term' }}</div>
                                        @if(!empty($endTermUpdate->next_resumption_date))
                                            <p class="mb-2"><strong>Resumption date:</strong> {{ $endTermUpdate->next_resumption_date->format('l, d F Y') }}</p>
                                        @endif
                                        @if(!empty($endTermUpdate->fee_deadline))
                                            <p class="mb-2"><strong>Fees deadline:</strong> {{ $endTermUpdate->fee_deadline->format('l, d F Y') }}</p>
                                        @endif
                                        @if(!empty($endTermUpdate->resumption_note))
                                            <div class="text-muted" style="white-space: pre-wrap;">{{ $endTermUpdate->resumption_note }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-info border-0 shadow-sm">
                                No end-of-term news has been published for {{ $semester->semester_name }} yet.
                            </div>
                        @endif
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
