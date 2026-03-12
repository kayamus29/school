@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-clock-history"></i> Log Details</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{route('audit.index')}}">Audit Logs</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Details #{{ $log->id }}</li>
                            </ol>
                        </nav>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light fw-bold">Metadata</div>
                                    <div class="card-body">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <th>ID:</th>
                                                <td>{{ $log->id }}</td>
                                            </tr>
                                            <tr>
                                                <th>UUID:</th>
                                                <td><small class="text-muted">{{ $log->batch_uuid ?? 'N/A' }}</small></td>
                                            </tr>
                                            <tr>
                                                <th>Date:</th>
                                                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Action:</th>
                                                <td><span
                                                        class="badge bg-primary">{{ strtoupper($log->description) }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Subject:</th>
                                                <td>{{ $log->subject_type }} ({{ $log->subject_id }})</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light fw-bold">Causer (Actor)</div>
                                    <div class="card-body">
                                        @if($log->causer)
                                            <div class="d-flex align-items-center">
                                                @if($log->causer->photo)
                                                    <img src="{{ asset('storage/' . $log->causer->photo) }}"
                                                        class="rounded-circle me-3" width="50" height="50">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center text-white"
                                                        style="width:50px; height:50px;">
                                                        {{ substr($log->causer->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $log->causer->first_name }}
                                                        {{ $log->causer->last_name }}</strong><br>
                                                    <span class="text-muted small">ID: {{ $log->causer->id }} | Email:
                                                        {{ $log->causer->email }}</span><br>
                                                    <span class="badge bg-info">{{ ucfirst($log->causer->role) }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <p class="text-muted mb-0 italic">System / Unauthenticated User</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-dark text-white fw-bold">Full Payload / Changes</div>
                            <div class="card-body">
                                <pre
                                    class="bg-light p-3 rounded"><code>{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</code></pre>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('audit.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
