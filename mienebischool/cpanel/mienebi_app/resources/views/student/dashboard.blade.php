@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-3">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-grid-fill"></i> My Dashboard</h1>
                        
                        <div class="row dashboard">
                            <div class="col-md-3">
                                <div class="card rounded-pill border-success shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center text-success">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><i class="bi bi-calendar-check-fill me-2"></i> Present</div>
                                            </div>
                                            <span class="badge bg-success rounded-pill">{{ $totalPresent }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card rounded-pill border-warning shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center text-warning">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><i class="bi bi-calendar-x-fill me-2"></i> Absent</div>
                                            </div>
                                            <span class="badge bg-warning rounded-pill">{{ $totalAbsent }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                <a href="{{ route('student.fees') }}" class="text-decoration-none">
                                    <div class="card rounded-pill border-{{ $walletBalance >= 0 ? 'success' : 'danger' }} shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center text-{{ $walletBalance >= 0 ? 'success' : 'danger' }}">
                                                <div class="ms-2 me-auto">
                                                    <div class="fw-bold"><i class="bi bi-wallet2 me-2"></i> {{ $walletBalance >= 0 ? 'My Credit' : 'My Debt' }}</div>
                                                </div>
                                                <span class="badge bg-{{ $walletBalance >= 0 ? 'success' : 'danger' }} rounded-pill">₦{{ number_format(abs($walletBalance), 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <div class="col-md-3">
                                <div class="card rounded-pill border-info shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center text-info">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><i class="bi bi-mortarboard-fill me-2"></i> 
                                                    {{ $promotion->schoolClass->class_name ?? 'N/A' }} 
                                                </div>
                                            </div>
                                            <i class="bi bi-info-circle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row align-items-md-stretch mt-4">
                            <div class="col-md-8">
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Recent Attendance</h5>
                                        <a href="{{ route('student.attendance') }}" class="btn btn-sm btn-outline-primary">View History</a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="ps-4">Date</th>
                                                        <th>Status</th>
                                                        <th>Class</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($recentAttendance as $record)
                                                        <tr>
                                                            <td class="ps-4">{{ $record->created_at->format('d M, Y') }}</td>
                                                            <td>
                                                                <span class="badge rounded-pill {{ $record->status == 'Present' ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $record->status }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $record->schoolClass->class_name ?? 'N/A' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center py-4 text-muted">No recent attendance records.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-transparent border-0">
                                        <h5 class="mb-0"><i class="bi bi-megaphone-fill me-2"></i> Latest Notices</h5>
                                    </div>
                                    <div class="card-body">
                                        @forelse($notices as $notice)
                                            <div class="mb-3 pb-3 border-bottom last-child-no-border">
                                                <h6 class="text-primary mb-1">{{ $notice->title ?? 'Notice' }}</h6>
                                                <p class="text-muted small mb-1">{!! Str::limit(strip_tags($notice->notice), 80) !!}</p>
                                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> {{ $notice->created_at->diffForHumans() }}</small>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted">No recent notices.</div>
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
@endsection
