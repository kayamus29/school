@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Staff Attendance</h1>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card shadow h-100 py-2 border-left-{{ $todayAttendance ? ($todayAttendance->check_out_at ? 'secondary' : 'success') : 'primary' }}">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Current Status</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="status-text">
                                            @if(!$todayAttendance)
                                                Not Checked In
                                            @elseif(!$todayAttendance->check_out_at)
                                                Checked In ({{ \Carbon\Carbon::parse($todayAttendance->check_in_at)->format('H:i') }})
                                            @else
                                                Shift Completed
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    @if(!$todayAttendance)
                                        <button id="btn-checkin" class="btn btn-primary w-100">
                                            <i class="bi bi-geo-alt"></i> Check In Now
                                        </button>
                                    @elseif(!$todayAttendance->check_out_at)
                                        <button id="btn-checkout" class="btn btn-warning w-100">
                                            <i class="bi bi-door-open"></i> Check Out
                                        </button>
                                    @else
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-check-all"></i> Already Processed
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Attendance History</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $record)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                            <td>{{ $record->check_in_at ? \Carbon\Carbon::parse($record->check_in_at)->format('H:i') : '-' }}</td>
                                            <td>{{ $record->check_out_at ? \Carbon\Carbon::parse($record->check_out_at)->format('H:i') : '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $record->status == 'late' ? 'danger' : 'success' }}">
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($record->check_out_at)
                                                    {{ \Carbon\Carbon::parse($record->check_in_at)->diff(\Carbon\Carbon::parse($record->check_out_at))->format('%hH %iM') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-2">
                                {{ $history->links() }}
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <script>
        const btnCheckin = document.getElementById('btn-checkin');
        const btnCheckout = document.getElementById('btn-checkout');

        function handleAttendance(action) {
            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser.");
                return;
            }

            const btn = action === 'checkin' ? btnCheckin : btnCheckout;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processing...';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const data = {
                        lat: position.coords.latitude,
                        long: position.coords.longitude,
                        _token: '{{ csrf_token() }}'
                    };

                    fetch(`/staff/attendance/${action === 'checkin' ? 'check-in' : 'check-out'}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert(result.message);
                            location.reload();
                        } else {
                            alert(result.message);
                            btn.innerHTML = originalHtml;
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert("An error occurred. Please try again.");
                        btn.innerHTML = originalHtml;
                        btn.disabled = false;
                    });
                },
                (error) => {
                    alert("Error getting location: " + error.message);
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
            );
        }

        btnCheckin?.addEventListener('click', () => handleAttendance('checkin'));
        btnCheckout?.addEventListener('click', () => handleAttendance('checkout'));
    </script>
@endsection

