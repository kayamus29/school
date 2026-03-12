@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-journal-richtext"></i>
                            {{ $isAdminView ? 'Lesson Plans' : 'My Lesson Plans' }}
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Lesson Plans</li>
                            </ol>
                        </nav>
                        @include('session-messages')
                        @if(Auth::user()->hasRole('Teacher'))
                            <div class="mb-3">
                                <a href="{{ route('lesson-plans.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Lesson Plan
                                </a>
                            </div>
                        @endif
                        <div class="table-responsive bg-white border shadow-sm p-3">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Teacher</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Subject</th>
                                        <th>Term</th>
                                        <th>Format</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($lessonPlans as $lessonPlan)
                                        <tr>
                                            <td>{{ $lessonPlan->title }}</td>
                                            <td>{{ optional($lessonPlan->teacher)->first_name }} {{ optional($lessonPlan->teacher)->last_name }}</td>
                                            <td>{{ optional($lessonPlan->schoolClass)->class_name }}</td>
                                            <td>{{ optional($lessonPlan->section)->section_name }}</td>
                                            <td>{{ optional($lessonPlan->course)->course_name }}</td>
                                            <td>{{ optional($lessonPlan->semester)->semester_name }}</td>
                                            <td>
                                                @if($lessonPlan->content)
                                                    <span class="badge bg-info text-dark">Typed</span>
                                                @endif
                                                @if($lessonPlan->file_path)
                                                    <span class="badge bg-secondary">File</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('lesson-plans.show', $lessonPlan->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                @if(Auth::user()->hasRole('Admin') || Auth::id() === $lessonPlan->teacher_id)
                                                    <a href="{{ route('lesson-plans.edit', $lessonPlan->id) }}" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-pencil"></i> {{ Auth::user()->hasRole('Admin') ? 'Review' : 'Edit' }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No lesson plans found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
