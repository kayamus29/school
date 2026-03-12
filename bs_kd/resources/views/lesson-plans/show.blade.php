@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3"><i class="bi bi-journal-text"></i> {{ $lessonPlan->title }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('lesson-plans.index') }}">Lesson Plans</a></li>
                                <li class="breadcrumb-item active" aria-current="page">View</li>
                            </ol>
                        </nav>

                        <div class="card border shadow-sm">
                            <div class="card-body">
                                <p><strong>Teacher:</strong> {{ optional($lessonPlan->teacher)->first_name }} {{ optional($lessonPlan->teacher)->last_name }}</p>
                                <p><strong>Class:</strong> {{ optional($lessonPlan->schoolClass)->class_name }}</p>
                                <p><strong>Section:</strong> {{ optional($lessonPlan->section)->section_name }}</p>
                                <p><strong>Subject:</strong> {{ optional($lessonPlan->course)->course_name }}</p>
                                <p><strong>Semester:</strong> {{ optional($lessonPlan->semester)->semester_name }}</p>

                                @if($lessonPlan->content)
                                    <hr>
                                    <h5>Typed Lesson Plan</h5>
                                    <div class="border rounded p-3 bg-light">{!! nl2br(e($lessonPlan->content)) !!}</div>
                                @endif

                                @if($lessonPlan->file_path)
                                    <hr>
                                    <a href="{{ asset('storage/' . $lessonPlan->file_path) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="bi bi-download"></i> Open {{ $lessonPlan->file_name ?? 'Lesson Plan File' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection
