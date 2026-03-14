@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-cloud-sun"></i> Give Marks
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{url()->previous()}}">My courses</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Give Marks</li>
                            </ol>
                        </nav>
                        @include('session-messages')
                        @if (optional($academic_setting)->marks_submission_status == "on")
                            <p class="text-primary">
                                <i class="bi bi-exclamation-diamond-fill me-2"></i> Marks Submission Window is open now.
                            </p>
                        @endif
                        <p class="text-primary">
                            <i class="bi bi-exclamation-diamond-fill me-2"></i> Final Marks submission should be done only
                            once in a Semester when the Marks Submission Window is open.
                        </p>
                        @if ($final_marks_submitted)
                            <p class="text-success">
                                <i class="bi bi-exclamation-diamond-fill me-2"></i> Marks are submitted.
                            </p>
                        @endif
                        <h3><i class="bi bi-diagram-2"></i> Class #{{request()->query('class_name')}}, Section
                            #{{request()->query('section_name')}}</h3>
                        <h3><i class="bi bi-compass"></i> Course: {{request()->query('course_name')}}</h3>
                        @if (!$final_marks_submitted && count($exams) > 0 && optional($academic_setting)->marks_submission_status == "on")
                            <div class="col-3 mt-3">
                                <a type="button"
                                    href="{{route('course.final.mark.submit.show', ['class_id' => $class_id, 'class_name' => request()->query('class_name'), 'section_id' => $section_id, 'section_name' => request()->query('section_name'), 'course_id' => $course_id, 'course_name' => request()->query('course_name'), 'semester_id' => $semester_id])}}"
                                    class="btn btn-outline-primary"
                                    onclick="return confirm('Are you sure, you want to submit final marks?')"><i
                                        class="bi bi-check2"></i> Submit Final Marks</a>
                            </div>
                        @endif
                        <form action="{{route('course.mark.store')}}" method="POST">
                            @csrf
                            <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                            <div class="row mt-3">
                                <div class="col">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" class="align-middle">Student Name</th>
                                                    @isset($exams)
                                                        @foreach ($exams as $exam)
                                                            @php
                                                                $breakdown = $exam->examRule->marks_breakdown ?? [
                                                                    ['name' => 'Final Exam', 'weight' => 70],
                                                                    ['name' => 'CA 1', 'weight' => 15],
                                                                    ['name' => 'CA 2', 'weight' => 15]
                                                                ];
                                                                $count = count($breakdown);
                                                            @endphp
                                                            <th colspan="{{$count}}" class="text-center">
                                                                <a href="{{route('exam.rule.show', ['exam_id' => $exam->id])}}"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="View {{$exam->exam_name}} rules">
                                                                    {{$exam->exam_name}}
                                                                </a>
                                                            </th>
                                                        @endforeach
                                                    @endisset
                                                </tr>
                                                <tr>
                                                    @isset($exams)
                                                        @foreach ($exams as $exam)
                                                            @php
                                                                $breakdown = $exam->examRule->marks_breakdown ?? [
                                                                    ['name' => 'Final Exam', 'weight' => 70],
                                                                    ['name' => 'CA 1', 'weight' => 15],
                                                                    ['name' => 'CA 2', 'weight' => 15]
                                                                ];
                                                            @endphp
                                                            @foreach($breakdown as $item)
                                                                <th style="min-width: 80px;">{{$item['name']}}</th>
                                                            @endforeach
                                                        @endforeach
                                                    @endisset
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @isset($exams)
                                                    @isset($students_with_marks)
                                                        @foreach ($students_with_marks as $id => $stu_marks)
                                                            @php
                                                                $student = $stu_marks->first()->student;
                                                            @endphp
                                                            <tr>
                                                                <td>{{$student->first_name}} {{$student->last_name}}</td>
                                                                @foreach ($exams as $exam)
                                                                    @php
                                                                        $mark = $stu_marks->where('exam_id', $exam->id)->first();
                                                                        $breakdown = $exam->examRule->marks_breakdown ?? [
                                                                            ['name' => 'Final Exam', 'weight' => 70],
                                                                            ['name' => 'CA 1', 'weight' => 15],
                                                                            ['name' => 'CA 2', 'weight' => 15]
                                                                        ];
                                                                    @endphp
                                                                    @foreach($breakdown as $item)
                                                                        @php
                                                                            $key = Str::slug($item['name'], '_');
                                                                            $val = 0;
                                                                            if ($mark && $mark->breakdown_marks && isset($mark->breakdown_marks[$key])) {
                                                                                $val = $mark->breakdown_marks[$key];
                                                                            } elseif ($mark) {
                                                                                if ($key == 'final_exam' || $key == 'exam')
                                                                                    $val = $mark->exam_mark;
                                                                                elseif ($key == 'ca_1' || $key == 'ca1')
                                                                                    $val = $mark->ca1_mark;
                                                                                elseif ($key == 'ca_2' || $key == 'ca2')
                                                                                    $val = $mark->ca2_mark;
                                                                            }
                                                                        @endphp
                                                                        <td>
                                                                            <input type="number" step="0.01"
                                                                                class="form-control form-control-sm"
                                                                                name="student_mark[{{$student->id}}][{{$exam->id}}][{{$key}}]"
                                                                                value="{{$val}}">
                                                                        </td>
                                                                    @endforeach
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    @endisset
                                                @endisset

                                                @if(isset($students_with_marks) && count($students_with_marks) < 1)
                                                    @foreach ($sectionStudents as $sectionStudent)
                                                        <tr>
                                                            <td>{{$sectionStudent->student->first_name}}
                                                                {{$sectionStudent->student->last_name}}
                                                            </td>
                                                            @isset($exams)
                                                                @foreach ($exams as $exam)
                                                                    @php
                                                                        $breakdown = $exam->examRule->marks_breakdown ?? [
                                                                            ['name' => 'Final Exam', 'weight' => 70],
                                                                            ['name' => 'CA 1', 'weight' => 15],
                                                                            ['name' => 'CA 2', 'weight' => 15]
                                                                        ];
                                                                    @endphp
                                                                    @foreach($breakdown as $item)
                                                                        @php
                                                                            $key = Str::slug($item['name'], '_');
                                                                        @endphp
                                                                        <td>
                                                                            <input type="number" step="0.01"
                                                                                class="form-control form-control-sm"
                                                                                name="student_mark[{{$sectionStudent->student->id}}][{{$exam->id}}][{{$key}}]"
                                                                                value="0">
                                                                        </td>
                                                                    @endforeach
                                                                @endforeach
                                                            @endisset
                                                        </tr>
                                                    @endforeach
                                                @endif
                                                <input type="hidden" name="semester_id" value="{{$semester_id}}">
                                                <input type="hidden" name="class_id" value="{{$class_id}}">
                                                <input type="hidden" name="section_id" value="{{$section_id}}">
                                                <input type="hidden" name="course_id" value="{{$course_id}}">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @if(!$final_marks_submitted && count($exams) > 0)
                                <div class="col-3 mt-3">
                                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-check2"></i> Save
                                        Marks</button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
@endsection