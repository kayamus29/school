@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h1 class="display-6 mb-0">
                                <i class="bi bi-mortarboard-fill"></i> Graduation Dashboard
                            </h1>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#gradSettingsModal">
                                <i class="bi bi-gear-fill me-1"></i> Graduation Settings
                            </button>
                        </div>
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Graduation Management</li>
                            </ol>
                        </nav>

                        @include('session-messages')

                        @if($finalClasses->isEmpty())
                            <div class="alert alert-warning border-0 shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>No final-year classes designated.</strong>
                                <p class="mb-0 small">Please go to <a href="/academics/settings">Academic Settings</a> and mark the appropriate classes as "Final Year" to see students here.</p>
                            </div>
                        @else
                            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                                <div class="card-header bg-white py-3 border-0">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="mb-0 fw-bold">Eligible Students for Graduation Evaluation</h6>
                                            <small class="text-muted">Filtered by current session and "Final Year" class designations.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="bg-light text-muted smallest text-uppercase">
                                            <tr>
                                                <th class="ps-4">Student</th>
                                                <th>Class</th>
                                                <th>Academic Status</th>
                                                <th>Financial Status</th>
                                                <th>Eligibility</th>
                                                <th class="text-end pe-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($gradData as $data)
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                                {{ substr($data['student']->first_name, 0, 1) }}{{ substr($data['student']->last_name, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold small">{{ $data['student']->first_name }} {{ $data['student']->last_name }}</div>
                                                                <div class="smallest text-muted">{{ $data['student']->email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="small">{{ $data['current_class'] }}</td>
                                                    <td>
                                                        @if($data['evaluation']['status'] == 'not_promoted')
                                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle smallest">Failed/Pending</span>
                                                        @else
                                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle smallest">Passed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(isset($data['evaluation']['balance']) && $data['evaluation']['balance'] > 0)
                                                            <div class="smallest text-danger fw-bold">₦{{ number_format($data['evaluation']['balance'], 2) }}</div>
                                                            <div class="smallest text-muted">Outstanding Balance</div>
                                                        @else
                                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle smallest">Cleared</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($data['evaluation']['status'] == 'eligible_for_graduation')
                                                            <span class="text-success small fw-bold"><i class="bi bi-patch-check-fill me-1"></i> Eligible</span>
                                                        @else
                                                            <span class="text-muted small" title="{{ $data['evaluation']['reason'] }}">
                                                                <i class="bi bi-x-circle-fill text-danger me-1"></i> Not Eligible
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        @if($data['evaluation']['status'] == 'eligible_for_graduation')
                                                            <form action="{{ route('academics.graduation.finalize', $data['student']->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to graduate this student? This action is permanent.');">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-primary">
                                                                    Finalize Graduation
                                                                </button>
                                                            </form>
                                                        @else
                                                            <button class="btn btn-sm btn-light disabled" title="{{ $data['evaluation']['reason'] }}">
                                                                Withheld
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="bi bi-people-fill display-4 d-block mb-3 opacity-25"></i>
                                                            No students found in designated final-year classes.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <div class="modal fade" id="gradSettingsModal" tabindex="-1" aria-labelledby="gradSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold" id="gradSettingsModalLabel">
                        <i class="bi bi-gear-fill text-secondary me-2"></i> Graduation Settings
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-primary small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Designate classes that should be considered "Final Year". Only students in these classes will appear in the graduation evaluation list.
                    </p>
                    <form action="{{route('school.final.grades.update')}}" method="POST">
                        @csrf
                        <div class="mb-4" style="max-height: 300px; overflow-y: auto;">
                            @isset($allClasses)
                                @foreach($allClasses as $class)
                                <div class="form-check py-1">
                                    <input class="form-check-input" type="checkbox" name="final_grade_classes[]" value="{{$class->id}}" id="modal_class_final_{{$class->id}}" {{$class->is_final_grade ? 'checked' : ''}}>
                                    <label class="form-check-label ps-2 fw-medium" for="modal_class_final_{{$class->id}}">
                                        {{$class->class_name}}
                                    </label>
                                </div>
                                @endforeach
                            @endisset
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold shadow-sm py-2">
                                <i class="bi bi-check2-circle me-1"></i> Update Designations
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .smallest { font-size: 0.72rem; }
        .bg-success-subtle { background-color: #d1e7dd; }
        .bg-danger-subtle { background-color: #f8d7da; }
        .bg-info-subtle { background-color: #cff4fc; }
    </style>
@endsection

