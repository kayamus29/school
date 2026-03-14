@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-envelope-open"></i> Inbox Message</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('communications.index') }}">Communication</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('communications.inbox') }}">Inbox</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Message</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="small text-muted">From</div>
                                <div class="fw-semibold">{{ $inboundEmail->from_name ?: 'Unknown' }}</div>
                                <div>{{ $inboundEmail->from_email }}</div>
                            </div>
                            <div class="mb-3">
                                <div class="small text-muted">Subject</div>
                                <div class="fw-semibold">{{ $inboundEmail->subject ?: '(No subject)' }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="small text-muted">Received</div>
                                <div>{{ optional($inboundEmail->received_at)->format('d M Y, h:i A') ?: 'N/A' }}</div>
                            </div>

                            @if($inboundEmail->body_html)
                                <div class="border rounded p-3 bg-light mb-3">{!! $inboundEmail->body_html !!}</div>
                            @else
                                <div class="border rounded p-3 bg-light mb-3" style="white-space: pre-wrap;">{{ $inboundEmail->body_text }}</div>
                            @endif

                            <a href="{{ route('communications.inbox.reply.form', $inboundEmail) }}" class="btn btn-primary">
                                <i class="bi bi-reply"></i> Reply
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
