@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Collect Payment</h1>
                    <a href="{{ route('accounting.payments.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to History
                    </a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">New Payment Record</h6>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('accounting.payments.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <!-- Student Selection -->
                                <div class="col-md-6 mb-3">
                                    <label for="student_id" class="form-label">Select Student <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select select2" name="student_id" id="student_select" required>
                                        <option value="">-- Choose Student --</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}">{{ $student->first_name }}
                                                {{ $student->last_name }} (ID: {{ $student->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Class Selection -->
                                <div class="col-md-6 mb-3">
                                    <label for="class_id" class="form-label">Class <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="class_id" required>
                                        @foreach($classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Session & Term -->
                                <div class="col-md-6 mb-3">
                                    <label for="school_session_id" class="form-label">Academic Session <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="school_session_id" required>
                                        @foreach($sessions as $s)
                                            <option value="{{ $s->id }}">{{ $s->session_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="POS">POS</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="semester_id" class="form-label">Term <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" name="semester_id" id="semester_id" required>
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem->id }}">{{ $sem->semester_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Payment Details -->
                                <div class="col-md-6 mb-3">
                                    <label for="transaction_date" class="form-label">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="transaction_date"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="amount_paid" class="form-label">Amount (₦) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="amount_paid"
                                        placeholder="0.00" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reference_no" class="form-label">Reference No (Optional)</label>
                                    <input type="text" class="form-control" name="reference_no"
                                        placeholder="Leave blank to auto-generate">
                                </div>
                                <!-- Remarks -->
                                <div class="col-md-6 mb-3">
                                    <label for="remarks" class="form-label">Remarks / Description</label>
                                    <input type="text" class="form-control" name="remarks" placeholder="Optional remarks">
                                </div>
                            </div>

                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Save
                                    Payment</button>
                            </div>
                        </form>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#student_select').on('change', function () {
                    var studentId = $(this).val();
                    var feeSelect = $('#fee_select');
                    var feeInfo = $('#fee_info');

                    // Reset fields
                    feeSelect.empty().append('<option value="">-- Loading Fees... --</option>');
                    feeInfo.text('');

                    if (studentId) {
                        // 1. Fetch Student Details (Auto-fill)
                        $.ajax({
                            url: "{{ url('school/accounting/payments/student') }}/" + studentId + "/details",
                            type: 'GET',
                            dataType: 'json',
                            success: function(details) {
                                if (details.class_id) {
                                    $('select[name="class_id"]').val(details.class_id).trigger('change');
                                }
                                if (details.session_id) {
                                    $('select[name="school_session_id"]').val(details.session_id).trigger('change');
                                }
                            }
                        });
                    } else {
                        feeSelect.empty().append('<option value="">-- Select Student First --</option>');
                        feeSelect.prop('disabled', true);
                    }
                });

                // Auto-fill amount when fee is selected
                $('#fee_select').on('change', function () {
                    var selectedOption = $(this).find('option:selected');
                    var amount = selectedOption.data('amount');
                    if (amount) {
                        $('input[name="amount_paid"]').val(amount);
                    }
                });
            });
        </script>
    @endpush
@endsection
