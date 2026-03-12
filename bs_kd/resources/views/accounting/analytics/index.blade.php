@extends('layouts.app')

@section('content')
    <div class="container text-darkings">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Financial Analytics</h1>
                    <form action="{{ route('accounting.analytics.index') }}" method="GET" class="d-flex align-items-center">
                        <select name="session_id" class="form-select me-2" onchange="this.form.submit()">
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ $session->id == $session_id ? 'selected' : '' }}>
                                    {{ $session->session_name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="row">
                    <!-- Metrics -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Received</div>
                                <div class="h5 mb-0 font-weight-bold">₦{{ number_format($totalRevenue, 2) }}</div>
                                <div class="progress progress-sm mt-2">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                        style="width: {{ $totalExpected > 0 ? ($totalRevenue / $totalExpected) * 100 : 0 }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($totalExpected > 0 ? ($totalRevenue / $totalExpected) * 100 : 0, 1) }}% of target</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Outstanding</div>
                                <div class="h5 mb-0 font-weight-bold">₦{{ number_format($totalOutstanding, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Target Revenue</div>
                                <div class="h5 mb-0 font-weight-bold">₦{{ number_format($totalExpected, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Revenue Growth Chart (Placeholder for now, or use Chart.js if available) -->
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue Growth ({{ date('Y') }})</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area" style="height: 300px;">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Type Distribution -->
                    <div class="col-lg-4 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Revenue by Type</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Revenue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($feeTypes as $type)
                                                <tr>
                                                    <td>{{ ucfirst($type->fee_type) }}</td>
                                                    <td>₦{{ number_format($type->revenue, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Top Classes -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Top Revenue Contributing Classes</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Class Name</th>
                                                <th>Revenue Contributed</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($classRevenue as $record)
                                                <tr>
                                                    <td>{{ $record->schoolClass->class_name ?? 'N/A' }}</td>
                                                    <td class="fw-bold text-primary">₦{{ number_format($record->total, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue (₦)',
                    data: @json($growthData),
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderColor: 'rgba(78, 115, 223, 1)',
                    pointRadius: 3,
                    pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                    pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₦' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₦' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection

