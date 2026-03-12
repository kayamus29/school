@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/fullcalendar5.9.0.min.css') }}">
    <script src="{{ asset('js/fullcalendar5.9.0.main.min.js') }}"></script>

    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-3">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-calendar2-check-fill"></i> My Attendance</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                            </ol>
                        </nav>

                        <div class="row mt-4">
                            <div class="col-md-7">
                                <div class="card shadow-sm border-0">
                                    <div class="card-header bg-transparent border-0">
                                        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i> Attendance Calendar</h5>
                                    </div>
                                    <div class="card-body bg-white rounded-bottom">
                                        <div id="attendanceCalendar"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="card shadow-sm border-0 h-100">
                                    <div
                                        class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="bi bi-list-stars me-2"></i> Recent History</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th class="ps-3">Date</th>
                                                        <th>Status</th>
                                                        <th>Course</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($attendance as $record)
                                                        <tr>
                                                            <td class="ps-3 small">{{ $record->created_at->format('d M, Y') }}
                                                            </td>
                                                            <td>
                                                                <span
                                                                    class="badge rounded-pill {{ $record->status == 'Present' || $record->status == 'on' ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ ($record->status == 'on' || $record->status == 'Present') ? 'Present' : 'Absent' }}
                                                                </span>
                                                            </td>
                                                            <td class="small">{{ $record->course->course_name ?? 'N/A' }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center py-5 text-muted small">No
                                                                history.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 small">
                                        {{ $attendance->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    @php
        $events = array();
        if (count($attendance) > 0) {
            foreach ($attendance as $record) {
                $isPresent = ($record->status == "on" || $record->status == "Present");
                $events[] = [
                    'title' => $isPresent ? "Present" : "Absent",
                    'start' => $record->created_at->toDateString(),
                    'color' => $isPresent ? '#2dce89' : '#f5365c'
                ];
            }
        }
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('attendanceCalendar');
            var attEvents = @json($events);

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 450,
                contentHeight: 400,
                events: attEvents,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                displayEventTime: false
            });
            calendar.render();
        });
    </script>

    <style>
        .fc-event {
            cursor: pointer;
        }

        .fc-toolbar-title {
            font-size: 1.1rem !important;
            font-weight: bold;
        }

        .fc-button {
            padding: 0.2rem 0.5rem !important;
            font-size: 0.8rem !important;
        }
    </style>
@endsection
