@extends('layouts.app')

@section('content')
    <style>
        /* .table th:first-child,
                .table td:first-child {
                  position: relative;
                  background-color: #f8f9fa;
                } */
    </style>
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-person-lines-fill"></i> Student
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{route('student.list.show')}}">Student List</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Profile</li>
                            </ol>
                        </nav>
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-sm-4 col-md-3">
                                    <div class="card bg-light">
                                        <div class="px-5 pt-2">
                                            @if (isset($student->photo))
                                                <img src="{{asset('/storage' . $student->photo)}}"
                                                    class="rounded-3 card-img-top" alt="Profile photo">
                                            @else
                                                <img src="{{asset('imgs/profile.png')}}" class="rounded-3 card-img-top"
                                                    alt="Profile photo">
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h5 class="card-title mb-0">{{$student->first_name}} {{$student->last_name}}
                                                </h5>
                                                @if($student->status == 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($student->status == 'deactivated')
                                                    <span class="badge bg-danger">Deactivated</span>
                                                @else
                                                    <span class="badge bg-info text-dark">Graduated</span>
                                                @endif
                                            </div>
                                            <p class="card-text text-muted mb-0 small">#ID:
                                                {{$promotion_info->id_card_number ?? 'N/A'}}
                                            </p>
                                        </div>
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">Gender: {{$student->gender}}</li>
                                            <li class="list-group-item">Phone: {{$student->phone}}</li>
                                            {{-- <li class="list-group-item"><a href="#">View Marks &amp; Results</a></li>
                                            --}}
                                        </ul>
                                        @if(Auth::user()->hasRole('Admin'))
                                            <div class="card-footer bg-white border-top-0 pt-0 pb-3 hstack gap-2">
                                                @if($student->status == 'active')
                                                    <button type="button" class="btn btn-sm btn-outline-danger w-100"
                                                        data-bs-toggle="modal" data-bs-target="#deactivateModal">
                                                        <i class="bi bi-person-x-fill me-1"></i> Deactivate
                                                    </button>
                                                @elseif($student->status == 'deactivated')
                                                    <form action="{{ route('student.reactivate', $student->id) }}" method="POST"
                                                        class="w-100">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                                            <i class="bi bi-person-check-fill me-1"></i> Reactivate
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if($student->status == 'deactivated')
                                        <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0">
                                            <h6 class="alert-heading fw-bold small mb-1">Deactivation Info:</h6>
                                            <p class="small mb-1">{{ $student->deactivation_reason }}</p>
                                            <div class="smallest text-muted">By:
                                                {{ $student->deactivator->first_name ?? 'System' }} on
                                                {{ $student->deactivated_at->format('M d, Y') }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-sm-8 col-md-9">
                                    <div class="p-3 mb-3 border rounded bg-white">
                                        <h6>Student Information</h6>
                                        <div class="table-responsive">
                                            <table class="table  mt-3">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">First Name:</th>
                                                        <td>{{$student->first_name}}</td>
                                                        <th>Last Name:</th>
                                                        <td>{{$student->last_name}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Email:</th>
                                                        <td>{{$student->email}}</td>
                                                        <th>Birthday:</th>
                                                        <td>{{$student->birthday}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Nationality:</th>
                                                        <td>{{$student->nationality}}</td>
                                                        <th>Religion:</th>
                                                        <td>{{$student->religion}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Address:</th>
                                                        <td>{{$student->address}}</td>
                                                        <th>Address2:</th>
                                                        <td>{{$student->address2}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">City:</th>
                                                        <td>{{$student->city}}</td>
                                                        <th>Zip:</th>
                                                        <td>{{$student->zip}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Blood Type:</th>
                                                        <td>{{$student->blood_type}}</td>
                                                        <th>Phone:</th>
                                                        <td>{{$student->phone}}</td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">Gender:</th>
                                                        <td colspan="3">{{$student->gender}}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="p-3 mb-3 border rounded bg-white">
                                            <h6>Parents' Information</h6>
                                            <div class="table-responsive">
                                                <table class="table  mt-3">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">Father's Name:</th>
                                                            <td>{{ optional($student->parent_info)->father_name ?? 'N/A' }}
                                                            </td>
                                                            <th>Mother's Name:</th>
                                                            <td>{{ optional($student->parent_info)->mother_name ?? 'N/A' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Father's Phone:</th>
                                                            <td>{{ optional($student->parent_info)->father_phone ?? 'N/A' }}
                                                            </td>
                                                            <th>Mother's Phone:</th>
                                                            <td>{{ optional($student->parent_info)->mother_phone ?? 'N/A' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Address:</th>
                                                            <td colspan="3">
                                                                {{ optional($student->parent_info)->parent_address ?? 'N/A' }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="p-3 mb-3 border rounded bg-white">
                                                <h6>Academic Information</h6>
                                                <div class="table-responsive">
                                                    <table class="table  mt-3">
                                                        <tbody>
                                                            <tr>
                                                                <th scope="row">Class:</th>
                                                                <td>{{ optional(optional($promotion_info)->section)->schoolClass->class_name ?? 'N/A' }}
                                                                </td>
                                                                <th>Board Reg. No.:</th>
                                                                <td>{{ optional($student->academic_info)->board_reg_no ?? 'N/A' }}
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th scope="row">Section:</th>
                                                                <td colspan="3">
                                                                    {{ optional(optional($promotion_info)->section)->section_name ?? 'N/A' }}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card bg-light mt-3">
                                            <div class="card-body">
                                                <h5 class="card-title"><i class="bi bi-wallet2"></i> Wallet Balance</h5>
                                                @php
                                                    $val = $walletBalance ?? 0;
                                                    $isCredit = $val >= 0;
                                                    $color = $isCredit ? 'text-success' : 'text-danger';
                                                    $status = $isCredit ? 'Credit (Prepaid)' : 'Debt (Owing)';
                                                @endphp
                                                <h3 class="{{ $color }} fw-bold">
                                                    ₦{{ number_format(abs($val), 2) }}
                                                </h3>
                                                <p class="card-text {{ $color }} small text-uppercase fw-bold">{{ $status }}
                                                </p>
                                                <a href="{{ route('accounting.payments.index', ['search' => $student->first_name]) }}"
                                                    class="btn btn-primary btn-sm w-100">
                                                    <i class="bi bi-clock-history"></i> View Payment History
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('layouts.footer')
                        </div>
                    </div>
                </div>

                @if($student->status == 'active')
                    <!-- Deactivation Modal -->
                    <div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <form action="{{ route('student.deactivate', $student->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title fw-bold" id="deactivateModalLabel">Deactivate Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-4">
                                        <div class="alert alert-danger border-0 small mb-4">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            This student will be blocked from logging in and excluded from future promotions and
                                            billing.
                                        </div>
                                        <div class="mb-3">
                                            <label for="reason" class="form-label fw-bold small">Reason for Deactivation<sup><i
                                                        class="bi bi-asterisk text-danger"></i></sup></label>
                                            <textarea class="form-control" name="reason" id="reason" rows="3"
                                                placeholder="e.g. Disciplinary action, Voluntary withdrawal, etc."
                                                required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger px-4">Confirm Deactivation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
@endsection