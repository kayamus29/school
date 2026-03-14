@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-gear-fill"></i> Promotion Policies
                        </h1>
                        @include('session-messages')

                        <div class="alert alert-info border-0 shadow-sm bg-light-info">
                            <strong><i class="bi bi-info-circle-fill"></i> Policy Logic:</strong>
                            <p class="mb-0 small text-muted">A policy defines how the system calculates the final status of
                                a student at the end of the academic session. Use this to automate the review process for
                                teachers.</p>
                        </div>

                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-body">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Calculation Method</th>
                                            <th>Threshold</th>
                                            <th>Probation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($classes as $class)
                                            @php $policy = $policies[$class->id] ?? null; @endphp
                                            <tr>
                                                <td><strong>{{ $class->class_name }}</strong></td>
                                                <td>
                                                    <span
                                                        class="badge {{ ($policy->calculation_method ?? '') == 'weighted_term_3' ? 'bg-warning' : 'bg-primary' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $policy->calculation_method ?? 'cumulative')) }}
                                                    </span>
                                                </td>
                                                <td>{{ $policy->passing_threshold ?? '50.00' }}%</td>
                                                <td>
                                                    <span
                                                        class="text-{{ ($policy->probation_logic ?? '') == 'promote_with_tag' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $policy->probation_logic ?? 'retain')) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                        data-bs-target="#editPolicy{{ $class->id }}">
                                                        <i class="bi bi-pencil-square"></i> Configure
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Modal -->
                                            <div class="modal fade" id="editPolicy{{ $class->id }}" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('promotions.policy.store') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="class_id" value="{{ $class->id }}">
                                                        <div class="modal-content border-0 shadow">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Policy: {{ $class->class_name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Calculation Method</label>
                                                                    <select name="calculation_method" class="form-select"
                                                                        required>
                                                                        <option value="cumulative" {{ ($policy->calculation_method ?? '') == 'cumulative' ? 'selected' : '' }}>Cumulative (Average of all terms)
                                                                        </option>
                                                                        <option value="weighted_term_3" {{ ($policy->calculation_method ?? '') == 'weighted_term_3' ? 'selected' : '' }}>Weighted
                                                                            (Term 3 Focus)</option>
                                                                    </select>
                                                                    <small class="text-muted">How the engine calculates the
                                                                        annual average.</small>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Passing Threshold (%)</label>
                                                                    <input type="number" step="0.01" name="passing_threshold"
                                                                        class="form-control"
                                                                        value="{{ $policy->passing_threshold ?? '50.00' }}"
                                                                        required>
                                                                    <small class="text-muted">Minimum average marks to be
                                                                        "Promoted".</small>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Probation Logic</label>
                                                                    <select name="probation_logic" class="form-select" required>
                                                                        <option value="retain" {{ ($policy->probation_logic ?? '') == 'retain' ? 'selected' : '' }}>Retain (Fail if
                                                                            below threshold)</option>
                                                                        <option value="promote_with_tag" {{ ($policy->probation_logic ?? '') == 'promote_with_tag' ? 'selected' : '' }}>Promote on Probation (If close
                                                                            to threshold)</option>
                                                                    </select>
                                                                    <small class="text-muted">Handling students who fall
                                                                        slightly below the threshold.</small>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">Save
                                                                    Changes</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
