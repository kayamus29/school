@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-person-lines-fill"></i> Export Students
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Export Students</li>
                            </ol>
                        </nav>

                        <div class="mb-4">
                            <form class="row g-3" action="{{route('student.export.run')}}" method="POST">
                                @csrf
                                <div class="row g-3">
                                    <h5>Filter by Class and Section</h5>
                                    <div class="col-md-4">
                                        <label for="class_id" class="form-label">Class</label>
                                        <select onchange="getSections(this);" class="form-select" id="class_id" name="class_id">
                                            <option value="0" selected>All Classes</option>
                                            @foreach ($school_classes as $school_class)
                                                <option value="{{$school_class->id}}">{{$school_class->class_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="section-select" class="form-label">Section</label>
                                        <select class="form-select" id="section-select" name="section_id">
                                            <option value="0" selected>All Sections</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                                <div class="row g-3">
                                    <h5>Select columns to export</h5>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="id" id="id" checked>
                                            <label class="form-check-label" for="id">
                                                ID
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="id_card_number" id="id_card_number" checked>
                                            <label class="form-check-label" for="id_card_number">
                                                ID Card Number
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="first_name" id="first_name" checked>
                                            <label class="form-check-label" for="first_name">
                                                First Name
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="last_name" id="last_name" checked>
                                            <label class="form-check-label" for="last_name">
                                                Last Name
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="email" checked>
                                            <label class="form-check-label" for="email">
                                                Email
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="birthday" id="birthday">
                                            <label class="form-check-label" for="birthday">
                                                Birthday
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="address" id="address">
                                            <label class="form-check-label" for="address">
                                                Address
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="phone" id="phone">
                                            <label class="form-check-label" for="phone">
                                                Phone
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="gender" id="gender">
                                            <label class="form-check-label" for="gender">
                                                Gender
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="class" id="class" checked>
                                            <label class="form-check-label" for="class">
                                                Class
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="columns[]" value="section" id="section" checked>
                                            <label class="form-check-label" for="section">
                                                Section
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Export</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
    <script>
        function getSections(obj) {
            var class_id = obj.options[obj.selectedIndex].value;

            var url = "{{route('get.sections.courses.by.classId')}}?class_id=" + class_id

            fetch(url)
                .then((resp) => resp.json())
                .then(function (data) {
                    var sectionSelect = document.getElementById('section-select');
                    sectionSelect.options.length = 0;
                    let defaultOption = new Option('All Sections', 0);
                    sectionSelect.add(defaultOption);
                    data.sections.forEach(function (section, key) {
                        sectionSelect.add(new Option(section.section_name, section.id));
                    });
                })
                .catch(function (error) {
                    console.log(error);
                });
        }
    </script>
@endsection
