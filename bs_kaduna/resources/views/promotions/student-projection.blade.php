@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-graph-up"></i> Promotion Projection
                        </h1>

                        @if(isset($review) && $review->is_finalized)
                            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">
                                <i class="bi bi-check-circle-fill me-3 display-6"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Official Result Out!</h5>
                                    <p class="mb-0">Your final promotion status for this session is:
                                        <strong>{{ strtoupper($review->final_status) }}</strong>.</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center">
                                <i class="bi bi-hourglass-split me-3 display-6"></i>
                                <div>
                                    <h5 class="alert-heading mb-1">Pending Official Review</h5>
                                    <p class="mb-0">This is a projection based on your current marks. Official promotion results
                                        will be released after administrative finalization.</p>
                                </div>
                            </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm text-center py-4 px-3 mb-4">
                                    <div class="text-muted small text-uppercase fw-bold mb-2">Annual Average</div>
                                    <div
                                        class="display-4 fw-bold {{ ($performance['total_avg'] ?? 0) >= 50 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($performance['total_avg'] ?? 0, 2) }}%
                                    </div>
                                    <hr class="my-3">
                                    <div class="text-muted small">Projected Status</div>
                                    <h3 class="mt-2">
                                        <span
                                            class="badge {{ ($review->final_status ?? 'retained') == 'promoted' ? 'bg-success' : 'bg-danger' }}">
                                            {{ strtoupper($review->final_status ?? ($performance['total_avg'] >= 50 ? 'PROMOTED' : 'RETAINED')) }}
                                        </span>
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-0 py-3">
                                        <h5 class="card-title mb-0">Course-wise Performance</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="ps-3">Course</th>
                                                    <th>Avg Score</th>
                                                    <th class="text-end pe-3">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($performance['courses'] ?? [] as $courseId => $c)
                                                    <tr>
                                                        <td class="ps-3"><strong>{{ $c['course_name'] }}</strong></td>
                                                        <td>
                                                            <div class="progress" style="height: 10px; width: 100px;">
                                                                <div class="progress-bar {{ $c['annual_avg'] >= 50 ? 'bg-success' : 'bg-danger' }}"
                                                                    role="progressbar" style="width: {{ $c['annual_avg'] }}%">
                                                                </div>
                                                            </div>
                                                            <small
                                                                class="fw-bold mt-1 d-block">{{ number_format($c['annual_avg'], 1) }}%</small>
                                                        </td>
                                                        <td class="text-end pe-3">
                                                            @if($c['annual_avg'] >= 50)
                                                                <span
                                                                    class="badge bg-soft-success text-success border border-success">PASSED</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-soft-danger text-danger border border-danger">FAILED</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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

    <style>
        .bg-soft-success {
            background-color: #e6f7ef;
        }

        .bg-soft-danger {
            background-color: #fceaea;
        }
    </style>
@endsection
