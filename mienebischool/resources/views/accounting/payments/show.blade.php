@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <!-- Action Bar (Hidden on Print) -->
                <div class="d-flex justify-content-between align-items-center mb-4 pt-3 no-print">
                    <h1 class="h3 mb-0">Payment Receipt</h1>
                    <div class="btn-group shadow-sm" role="group">
                        <a href="{{ route('accounting.payments.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="bi bi-printer"></i> Print Receipt
                        </button>
                        <button onclick="downloadPDF()" class="btn btn-success">
                            <i class="bi bi-file-earmark-pdf"></i> Download PDF
                        </button>
                    </div>
                </div>

                <!-- Receipt Card -->
                <div class="card shadow-lg border-0 receipt-card">
                    <div class="card-body p-5">
                        <!-- Header with Logo -->
                        <div class="row mb-4 pb-4 border-bottom border-2">
                            <div class="col-md-3 text-center">
                                @if($site_setting && $site_setting->school_logo)
                                    <img src="{{ asset('storage/' . $site_setting->school_logo) }}" alt="School Logo"
                                        class="img-fluid mb-2" style="max-height: 100px; object-fit: contain;">
                                @else
                                    <div class="school-logo-placeholder mb-2">
                                        <i class="bi bi-mortarboard-fill text-primary" style="font-size: 4rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <h2 class="fw-bold text-primary mb-1">{{ $site_setting->school_name ?? config('app.name') }}
                                </h2>
                                @if($site_setting && $site_setting->school_address)
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-geo-alt-fill"></i> {{ $site_setting->school_address }}
                                    </p>
                                @endif
                                @if($site_setting && $site_setting->school_phone)
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-telephone-fill"></i> {{ $site_setting->school_phone }}
                                        @if($site_setting->school_email)
                                            <span class="ms-3"><i class="bi bi-envelope-fill"></i>
                                                {{ $site_setting->school_email }}</span>
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Receipt Title & Status -->
                        <div class="text-center mb-4">
                            <h3 class="receipt-title mb-2">OFFICIAL PAYMENT RECEIPT</h3>
                            <span class="badge bg-success px-4 py-2 fs-6">
                                <i class="bi bi-check-circle-fill"></i> PAID
                            </span>
                        </div>

                        <!-- Receipt Details Grid -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="detail-box mb-3">
                                    <label class="text-muted small text-uppercase fw-bold">Receipt Number</label>
                                    <p class="h5 text-primary mb-0">{{ $payment->reference_no }}</p>
                                </div>
                                <div class="detail-box mb-3">
                                    <label class="text-muted small text-uppercase fw-bold">Payment Date</label>
                                    <p class="h6 mb-0">
                                        <i class="bi bi-calendar-check text-primary"></i>
                                        {{ \Carbon\Carbon::parse($payment->transaction_date)->format('l, d F Y') }}
                                    </p>
                                </div>
                                <div class="detail-box mb-3">
                                    <label class="text-muted small text-uppercase fw-bold">Payment Method</label>
                                    <p class="h6 mb-0">
                                        <i class="bi bi-credit-card text-primary"></i>
                                        {{ ucfirst($payment->payment_method ?? 'Cash') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="student-info-card bg-light rounded p-3 border">
                                    <h6 class="text-uppercase fw-bold text-primary mb-3">
                                        <i class="bi bi-person-circle"></i> Student Information
                                    </h6>
                                    <div class="mb-2">
                                        <label class="text-muted small">Full Name</label>
                                        <p class="mb-0 fw-bold">{{ $payment->student->first_name }}
                                            {{ $payment->student->last_name }}</p>
                                    </div>
                                    <div class="mb-2">
                                        <label class="text-muted small">Admission Number</label>
                                        <p class="mb-0">{{ $payment->student->admission_number ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-2">
                                        <label class="text-muted small">Class</label>
                                        <p class="mb-0">{{ $payment->schoolClass->class_name }}</p>
                                    </div>
                                    <div>
                                        <label class="text-muted small">Session / Term</label>
                                        <p class="mb-0">{{ $payment->session->session_name }} -
                                            {{ $payment->semester->semester_name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Breakdown Table -->
                        <div class="table-responsive mb-4">
                            <table class="table table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th class="border-0">Description</th>
                                        <th class="border-0 text-center">Session / Term</th>
                                        <th class="border-0 text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong>School Fees Payment</strong><br>
                                            <small
                                                class="text-muted">{{ $payment->description ?? 'Tuition and other charges' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark">
                                                {{ $payment->session->session_name }}<br>
                                                {{ $payment->semester->semester_name }}
                                            </span>
                                        </td>
                                        <td class="text-end fs-5">₦{{ number_format($payment->amount_paid, 2) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="table-success border-top border-2">
                                        <td colspan="2" class="text-end fw-bold fs-5">TOTAL AMOUNT PAID</td>
                                        <td class="text-end fw-bold fs-4 text-success">
                                            ₦{{ number_format($payment->amount_paid, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Payment Summary Badge -->
                        <div class="alert alert-info bg-gradient border-0 shadow-sm mb-4" role="alert">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="alert-heading mb-1">
                                        <i class="bi bi-info-circle-fill"></i> Payment Confirmation
                                    </h6>
                                    <p class="mb-0 small">
                                        This is to certify that the amount of
                                        <strong>₦{{ number_format($payment->amount_paid, 2) }}</strong>
                                        has been received from <strong>{{ $payment->student->first_name }}
                                            {{ $payment->student->last_name }}</strong>
                                        for school fees payment.
                                    </p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="qr-placeholder bg-white p-2 rounded d-inline-block">
                                        <i class="bi bi-qr-code" style="font-size: 3rem;"></i>
                                        <p class="small mb-0 text-muted">Scan to verify</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer with Signature -->
                        <div class="row mt-5 pt-4 border-top">
                            <div class="col-md-6">
                                <div class="signature-box">
                                    <div class="signature-line mb-2"></div>
                                    <p class="mb-0 small text-muted">Authorized Signature</p>
                                    <p class="small fw-bold">Bursar / Accounts Officer</p>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="signature-box">
                                    <div class="signature-line mb-2"></div>
                                    <p class="mb-0 small text-muted">Official Stamp</p>
                                    <p class="small fw-bold">{{ $site_setting->school_name ?? config('app.name') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Footer Note -->
                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="small text-muted mb-1">
                                <i class="bi bi-shield-check"></i> This is a computer-generated receipt and is valid without
                                signature.
                            </p>
                            <p class="small text-muted mb-0">
                                Generated on: {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }} |
                                Printed by: {{ auth()->user()->first_name ?? 'System' }}
                            </p>
                        </div>
                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .receipt-card {
                box-shadow: none !important;
                border: 2px solid #ddd !important;
            }

            .card-body {
                padding: 2rem !important;
            }

            @page {
                margin: 1cm;
                size: A4;
            }
        }

        .receipt-card {
            background: white;
            max-width: 900px;
            margin: 0 auto;
        }

        .receipt-title {
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 2px;
        }

        .detail-box label {
            margin-bottom: 0.25rem;
            display: block;
        }

        .student-info-card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .signature-line {
            height: 60px;
            border-bottom: 2px solid #333;
            width: 250px;
            margin: 0 auto;
        }

        .qr-placeholder {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .school-logo-placeholder {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .school-logo-placeholder i {
            color: white !important;
        }
    </style>

    <script>
        function downloadPDF() {
            // Placeholder for PDF download functionality
            // You would integrate with a PDF library like jsPDF or send to backend
            alert('PDF download feature - integrate with backend endpoint or jsPDF library');
        }
    </script>
@endsection