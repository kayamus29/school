@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-clock-history"></i> Audit Logs</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Audit Logs</li>
                        </ol>
                    </nav>

                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4">Date & Time</th>
                                            <th>User (Actor)</th>
                                            <th>Action</th>
                                            <th>Object</th>
                                            <th>Changes</th>
                                            <th class="text-end pe-4">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($logs as $log)
                                        <tr>
                                            <td class="ps-4 text-muted small">
                                                {{ $log->created_at->toDayDateTimeString() }}
                                            </td>
                                            <td>
                                                @if($log->causer)
                                                    <span class="fw-bold">{{ $log->causer->first_name }} {{ $log->causer->last_name }}</span>
                                                    <br><small class="text-muted">{{ ucfirst($log->causer->role) }}</small>
                                                @else
                                                    <span class="text-muted">System / Unknown</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match($log->description) {
                                                        'created' => 'success',
                                                        'updated' => 'info',
                                                        'deleted' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }} text-uppercase">{{ $log->description }}</span>
                                            </td>
                                            <td>
                                                {{ class_basename($log->subject_type) }}
                                                <small class="text-muted text-nowrap">(ID: {{ $log->subject_id }})</small>
                                            </td>
                                            <td>
                                                @if(isset($log->properties['attributes']))
                                                    <details>
                                                        <summary class="small text-primary cursor-pointer">View Changes</summary>
                                                        <ul class="list-unstyled small mt-2">
                                                            @foreach($log->properties['attributes'] as $key => $value)
                                                                @if($key !== 'updated_at' && $key !== 'created_at')
                                                                <li>
                                                                    <strong>{{ $key }}:</strong> 
                                                                    @if(isset($log->properties['old'][$key]))
                                                                        <span class="text-danger"><del>{{ is_array($log->properties['old'][$key]) ? json_encode($log->properties['old'][$key]) : $log->properties['old'][$key] }}</del></span>
                                                                        <i class="bi bi-arrow-right mx-1"></i>
                                                                    @endif
                                                                    <span class="text-success">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                                                </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </details>
                                                @else
                                                    <span class="text-muted small">N/A</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <a href="{{ route('audit.show', $log->id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

