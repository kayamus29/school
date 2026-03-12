@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @include('layouts.left-menu')
            <div class="col-md-10 mt-5">
                <div class="card border-danger shadow">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i> Access Restricted</h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-lock-fill text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="text-dark">Outstanding Balance Detected</h3>
                        <p class="lead text-muted">You have an outstanding balance of <span
                                class="text-danger fw-bold">₦{{ number_format($balance, 2) }}</span>.</p>
                        <p>According to school policy, marks and results are restricted until all pending fees are settled.
                        </p>
                        <hr class="my-4">
                        <div class="d-grid gap-2 d-md-block">
                            <a href="{{ url('/') }}" class="btn btn-secondary px-4">Back to Dashboard</a>
                            {{-- Suggesting where to pay --}}
                            <button class="btn btn-primary px-4" disabled>Pay Online (Coming Soon)</button>
                        </div>
                    </div>
                    <div class="card-footer text-muted text-center small">
                        If you have already made a payment, please wait for administrative approval.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
