@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-file-plus"></i> Create Grading System</h1>
                        @include('session-messages')
                        <div class="row">
                            <div class="col-md-5 mb-4">
                                <div class="p-3 border shadow-sm bg-light">
                                    <form action="{{route('exam.grade.system.store')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                        <div>
                                            <p class="mt-2">Select class(es):<sup><i
                                                        class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select" name="class_ids[]" multiple required
                                                style="height: 150px;">
                                                @isset($school_classes)
                                                    @foreach ($school_classes as $school_class)
                                                        <option value="{{$school_class->id}}">{{$school_class->class_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                            <small class="text-muted">Hold Ctrl to select multiple</small>
                                        </div>
                                        <div>
                                            <p class="mt-2">Select semester:<sup><i
                                                        class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select" aria-label=".form-select-sm" name="semester_id"
                                                required>
                                                @isset($semesters)
                                                    @foreach ($semesters as $semester)
                                                        <option value="{{$semester->id}}"
                                                            {{($semester->id === request()->query('semester_id')) ? 'selected' : ''}}>
                                                            {{$semester->semester_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <div class="mt-2">
                                            <p>Grading System name<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <input type="text" class="form-control" placeholder="Grading System 1"
                                                aria-label="Grading System 1" name="system_name" required
                                                value="{{ old('system_name') }}">
                                        </div>
                                        <div id="weights-container" class="mt-3">
                                            <p class="mb-2">Marks breakdown<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <div class="weight-row mb-2 border p-2 bg-white rounded shadow-sm">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Label</label>
                                                        <input type="text" name="names[]" class="form-control form-control-sm"
                                                            value="{{ old('names.0', 'Final Exam') }}" required>
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label small mb-1">Weight (%)</label>
                                                        <input type="number" name="weights[]" class="form-control form-control-sm"
                                                            value="{{ old('weights.0', 70) }}" required min="0" max="100" step="0.01">
                                                    </div>
                                                    <div class="col-2 text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-weight-row">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="weight-row mb-2 border p-2 bg-white rounded shadow-sm">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Label</label>
                                                        <input type="text" name="names[]" class="form-control form-control-sm"
                                                            value="{{ old('names.1', 'CA') }}" required>
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label small mb-1">Weight (%)</label>
                                                        <input type="number" name="weights[]" class="form-control form-control-sm"
                                                            value="{{ old('weights.1', 30) }}" required min="0" max="100" step="0.01">
                                                    </div>
                                                    <div class="col-2 text-end">
                                                        <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-weight-row">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-grid gap-2 mt-2">
                                            <button type="button" id="add-weight-row" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-plus-lg"></i> Add Component
                                            </button>
                                        </div>
                                        <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                class="bi bi-check2"></i> Create</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
    <script>
        document.getElementById('add-weight-row').addEventListener('click', function () {
            const container = document.getElementById('weights-container');
            const newRow = document.createElement('div');
            newRow.className = 'weight-row mb-2 border p-2 bg-white rounded shadow-sm';
            newRow.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-6">
                        <label class="form-label small mb-1">Label</label>
                        <input type="text" name="names[]" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small mb-1">Weight (%)</label>
                        <input type="number" name="weights[]" class="form-control form-control-sm" required min="0" max="100" step="0.01">
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-weight-row">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.remove-weight-row')) {
                return;
            }

            const rows = document.querySelectorAll('.weight-row');
            if (rows.length > 1) {
                e.target.closest('.weight-row').remove();
            } else {
                alert('At least one component is required.');
            }
        });
    </script>
@endsection
