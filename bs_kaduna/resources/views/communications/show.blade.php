@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-clock-history"></i> Communication Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('communications.index') }}">Communication</a></li>
                            <li class="breadcrumb-item active" aria-current="page">History Item</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <div class="small text-muted">Channel</div>
                                    <div class="fw-semibold text-uppercase">{{ $communication->channel }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted">Scope</div>
                                    <div class="fw-semibold text-uppercase">{{ $communication->audience_type }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted">Status</div>
                                    <div class="fw-semibold">{{ ucfirst($communication->status) }}</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted">Sent By</div>
                                    <div class="fw-semibold">{{ optional($communication->creator)->first_name }} {{ optional($communication->creator)->last_name }}</div>
                                </div>
                            </div>

                            <hr>

                            @if($communication->subject)
                                <h5>{{ $communication->subject }}</h5>
                            @endif

                            @if($communication->channel === 'email' && $communication->message_html)
                                <div class="border rounded p-3 bg-light">{!! $communication->message_html !!}</div>
                            @else
                                <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;">{{ $communication->message }}</div>
                            @endif

                            <div class="row g-3 mt-3">
                                <div class="col-md-4">
                                    <div class="small text-muted">Qualified Recipients</div>
                                    <div class="fw-semibold">{{ $communication->total_recipients }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Successful</div>
                                    <div class="fw-semibold text-success">{{ $communication->successful_recipients }}</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted">Failed</div>
                                    <div class="fw-semibold text-danger">{{ $communication->failed_recipients }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5 class="card-title">Recipient History</h5>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Destination</th>
                                            <th>Status</th>
                                            <th>Sent At</th>
                                            <th>Error</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($communication->recipients as $recipient)
                                            <tr>
                                                <td>{{ $recipient->recipient_name }}</td>
                                                <td>{{ $recipient->destination }}</td>
                                                <td>
                                                    <span class="badge {{ $recipient->status === 'sent' ? 'bg-success' : ($recipient->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}">
                                                        {{ ucfirst($recipient->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ optional($recipient->sent_at)->format('d M Y, h:i A') ?: 'N/A' }}</td>
                                                <td class="text-danger">{{ $recipient->error_message ?: 'N/A' }}</td>
                                                <td>
                                                    @if(Auth::user()->hasRole('Admin') && $communication->channel === 'email' && $recipient->status === 'sent')
                                                        <a href="{{ route('communications.reply.form', [$communication, $recipient]) }}" class="btn btn-sm btn-outline-primary">
                                                            Reply
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-muted">No recipient logs available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
