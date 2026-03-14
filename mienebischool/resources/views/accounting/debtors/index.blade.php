@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
                    <h1 class="h3 mb-0">Debtors List</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('accounting.dashboard') }}">Accounting</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Debtors</li>
                        </ol>
                    </nav>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-circle me-2"></i> Students Owning
                                    Fees</h5>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('accounting.debtors.index') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Search by name or ID..." value="{{ request('search') }}">
                                        <button class="btn btn-outline-secondary" type="submit"><i
                                                class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Student Name</th>
                                        <th>Admission No.</th>
                                        <th>Class</th>
                                        <th class="text-end pe-4">Amount Owed</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($debtors as $student)
                                        @php
                                            // Wallet balance is negative for debt, so we take absolute value
                                            $debt = abs($student->wallet->balance);
                                            $promo = $student->promotions->first();
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                <div class="small text-muted">{{ $student->email }}</div>
                                            </td>
                                            <td>{{ $promo->id_card_number ?? 'N/A' }}</td>
                                            <td>
                                                @if($promo && $promo->schoolClass)
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                        {{ $promo->schoolClass->class_name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fst-italic">No Class</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="text-danger fw-bold">
                                                    ₦{{ number_format($debt, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="btn-group">
                                                    <a href="{{ route('accounting.payments.index', ['search' => $promo->id_card_number ?? '']) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Collect Payment">
                                                        <i class="bi bi-credit-card"></i> Pay
                                                    </a>
                                                    <a href="{{ route('accounting.fees.student.index', ['student_id' => $student->id]) }}"
                                                        class="btn btn-sm btn-outline-secondary" title="View Fee History">
                                                        <i class="bi bi-receipt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted display-6 mb-3">
                                                    <i class="bi bi-check-circle text-success"></i>
                                                </div>
                                                <p class="h5 text-muted">No debtors found!</p>
                                                <p class="small text-muted">All clear. Good job!</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3">
                        {{ $debtors->links() }}
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection