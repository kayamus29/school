@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-inbox"></i> Inbox</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('communications.index') }}">Communication</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Inbox</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Mailbox Messages</h5>
                                <form method="POST" action="{{ route('communications.inbox.sync') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-arrow-repeat"></i> Sync Inbox
                                    </button>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>From</th>
                                            <th>Subject</th>
                                            <th>Received</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($emails as $email)
                                            <tr>
                                                <td>
                                                    <div>{{ $email->from_name ?: 'Unknown' }}</div>
                                                    <div class="small text-muted">{{ $email->from_email }}</div>
                                                </td>
                                                <td>{{ $email->subject ?: '(No subject)' }}</td>
                                                <td>{{ optional($email->received_at)->format('d M Y, h:i A') ?: 'N/A' }}</td>
                                                <td>
                                                    <span class="badge {{ $email->is_seen ? 'bg-secondary' : 'bg-success' }}">
                                                        {{ $email->is_seen ? 'Seen' : 'New' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('communications.inbox.show', $email) }}" class="btn btn-sm btn-outline-primary">Open</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-muted">No inbox messages have been synced yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                {{ $emails->links() }}
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
