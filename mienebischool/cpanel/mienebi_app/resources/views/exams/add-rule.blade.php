@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-file-plus"></i> Add Exam Rule</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url()->previous()}}">Exams</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Add Exam Rule</li>
                            </ol>
                        </nav>
                        @include('session-messages')
                        <div class="row">
                            <div class="col-5 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <form action="{{route('exam.rule.store')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="exam_id" value="{{$exam_id}}">
                                        <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                        <div class="mt-2">
                                            <label for="inputTotalMarks" class="form-label">Total Marks<sup><i
                                                        class="bi bi-asterisk text-primary"></i></sup></label>
                                            <input type="number" class="form-control" id="inputTotalMarks"
                                                placeholder="10, 100, ..." name="total_marks" step="0.01">
                                        </div>
                                        <div class="mt-2">
                                            <label for="inputPassMarks" class="form-label">Pass Marks<sup><i
                                                        class="bi bi-asterisk text-primary"></i></sup></label>
                                            <input type="number" class="form-control" id="inputPassMarks"
                                                placeholder="5, 33, ..." name="pass_marks" step="0.01">
                                        </div>
                                        <div id="weights-container" class="mt-3">
                                            <div class="weight-row mb-2 border p-2 bg-white rounded shadow-sm">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Label (e.g. Exam)</label>
                                                        <input type="text" name="names[]"
                                                            class="form-control form-control-sm" value="Final Exam"
                                                            required>
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label small mb-1">Weight (%)</label>
                                                        <input type="number" name="weights[]"
                                                            class="form-control form-control-sm" value="70" required min="0"
                                                            max="100">
                                                    </div>
                                                    <div class="col-2 text-end">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger border-0 remove-weight-row"><i
                                                                class="bi bi-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="weight-row mb-2 border p-2 bg-white rounded shadow-sm">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Label (e.g. CA 1)</label>
                                                        <input type="text" name="names[]"
                                                            class="form-control form-control-sm" value="CA 1" required>
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label small mb-1">Weight (%)</label>
                                                        <input type="number" name="weights[]"
                                                            class="form-control form-control-sm" value="15" required min="0"
                                                            max="100">
                                                    </div>
                                                    <div class="col-2 text-end">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger border-0 remove-weight-row"><i
                                                                class="bi bi-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="weight-row mb-2 border p-2 bg-white rounded shadow-sm">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-6">
                                                        <label class="form-label small mb-1">Label (e.g. CA 2)</label>
                                                        <input type="text" name="names[]"
                                                            class="form-control form-control-sm" value="CA 2" required>
                                                    </div>
                                                    <div class="col-4">
                                                        <label class="form-label small mb-1">Weight (%)</label>
                                                        <input type="number" name="weights[]"
                                                            class="form-control form-control-sm" value="15" required min="0"
                                                            max="100">
                                                    </div>
                                                    <div class="col-2 text-end">
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger border-0 remove-weight-row"><i
                                                                class="bi bi-trash"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-grid gap-2 mt-2 mb-3">
                                            <button type="button" id="add-weight-row"
                                                class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus-lg"></i> Add
                                                Component</button>
                                        </div>
                                        <div class="mt-2">
                                            <label for="inputMarksDistributionNote" class="form-label">Marks Distribution
                                                Note<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                            <textarea class="form-control" id="inputMarksDistributionNote" rows="2"
                                                placeholder="Written: 7, MCQ: 3, ..."
                                                name="marks_distribution_note"></textarea>
                                        </div>
                                        <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i
                                                class="bi bi-plus"></i> Add</button>
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
                            <label class="form-label small mb-1">Label (e.g. Exam)</label>
                            <input type="text" name="names[]" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label small mb-1">Weight (%)</label>
                            <input type="number" name="weights[]" class="form-control form-control-sm" required min="0" max="100">
                        </div>
                        <div class="col-2 text-end">
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-weight-row"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                `;
            container.appendChild(newRow);
        });

        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-weight-row')) {
                const rows = document.querySelectorAll('.weight-row');
                if (rows.length > 1) {
                    e.target.closest('.weight-row').remove();
                } else {
                    alert('At least one component is required.');
                }
            }
        });
    </script>
@endsection
