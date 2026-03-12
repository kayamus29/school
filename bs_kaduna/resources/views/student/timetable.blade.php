@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-3">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-calendar3"></i> Class Timetable</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Timetable</li>
                            </ol>
                        </nav>

                        <div class="card shadow-sm border-0 mt-4">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle mb-0">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th style="width: 15%;">Time</th>
                                                <th>Entry</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                            @endphp
                                            @foreach($days as $day)
                                                @php
                                                    $dayRoutines = $routines->filter(fn($r) => $r->weekday == $day);
                                                @endphp
                                                @if($dayRoutines->count() > 0)
                                                    <tr class="table-secondary">
                                                        <td colspan="2" class="fw-bold ps-3">{{ $day }}</td>
                                                    </tr>
                                                    @foreach($dayRoutines as $routine)
                                                        <tr>
                                                            <td class="text-center small">
                                                                {{ \Carbon\Carbon::parse($routine->start)->format('h:i A') }} -
                                                                {{ \Carbon\Carbon::parse($routine->end)->format('h:i A') }}
                                                            </td>
                                                            <td class="ps-3">
                                                                <div class="fw-bold">{{ $routine->course->course_name ?? 'Course' }}
                                                                </div>
                                                                <div class="text-muted small">
                                                                    <i class="bi bi-person me-1"></i>
                                                                    {{-- Teacher info unavailable --}}
                                                                    {{-- $routine->teacher->first_name ?? 'N/A' --}}
                                                                    <span class="text-muted fst-italic">Instructor</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                            @if($routines->count() == 0)
                                                <tr>
                                                    <td colspan="2" class="text-center py-5 text-muted">No timetable entries
                                                        found for your section.</td>
                                                </tr>
                                            @endif
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
@endsection