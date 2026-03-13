@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-calendar-day"></i> Set Total School Days
                    </h1>

                    @include('session-messages')

                    <div class="p-3 border bg-light shadow-sm">
                        <h6>Configure Total School Days Per Semester</h6>
                        <p class="text-primary">
                            <small><i class="bi bi-info-circle-fill me-2"></i> Set the total number of days school was open for each semester. This value is used for attendance calculations.</small>
                        </p>
                        <form action="{{ route('school.total.school.days.update') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="semester_id" class="form-label">Select Semester<sup><i
                                            class="bi bi-asterisk text-primary"></i></sup></label>
                                <select class="form-select form-select-sm" id="semester_id" name="semester_id" required>
                                    <option value="" disabled selected>Please select a semester</option>
                                    @isset($semesters)
                                        @foreach ($semesters as $semester)
                                            <option value="{{ $semester->id }}" data-total-days="{{ $semester->total_school_days ?? 0 }}">
                                                {{ $semester->semester_name }} ({{ $semester->start_date }} to {{ $semester->end_date }})
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="total_school_days" class="form-label">Total School Days<sup><i
                                            class="bi bi-asterisk text-primary"></i></sup></label>
                                <input type="number" class="form-control form-control-sm" id="total_school_days"
                                    name="total_school_days" placeholder="e.g., 180" min="0" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Save</button>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const semesterSelect = document.getElementById('semester_id');
        const totalSchoolDaysInput = document.getElementById('total_school_days');

        semesterSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const totalDays = selectedOption.getAttribute('data-total-days');
            totalSchoolDaysInput.value = totalDays !== null ? totalDays : '';
        });

        // Initialize the input if a semester is pre-selected (e.g., after a form submission error)
        if (semesterSelect.value) {
            const selectedOption = semesterSelect.options[semesterSelect.selectedIndex];
            const totalDays = selectedOption.getAttribute('data-total-days');
            totalSchoolDaysInput.value = totalDays !== null ? totalDays : '';
        }
    });
</script>
@endsection
