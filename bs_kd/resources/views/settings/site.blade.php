@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="row pt-2">
                    <div class="col ps-4">
                        <h1 class="display-6 mb-3">
                            <i class="bi bi-gear-fill"></i> Site Settings
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Site Settings</li>
                            </ol>
                        </nav>

                        @include('session-messages')

                        <div class="card mb-4">
                            <div class="card-header">{{ __('Site Settings') }}</div>

                            <div class="card-body">
                                <form method="POST" action="{{ route('settings.site.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="school_name" class="form-label">School Name</label>
                                        <input type="text" class="form-control" id="school_name" name="school_name"
                                            value="{{ old('school_name', $setting->school_name) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="primary_color" class="form-label">Primary Color</label>
                                        <input type="color" class="form-control form-control-color" id="primary_color"
                                            name="primary_color" value="{{ old('primary_color', $setting->primary_color) }}"
                                            title="Choose your color" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="secondary_color" class="form-label">Secondary Color</label>
                                        <input type="color" class="form-control form-control-color" id="secondary_color"
                                            name="secondary_color"
                                            value="{{ old('secondary_color', $setting->secondary_color ?? '#ffffff') }}"
                                            title="Choose your color">
                                    </div>

                                    <div class="mb-3">
                                        <label for="school_logo" class="form-label">School Logo</label>
                                        <input class="form-control" type="file" id="school_logo" name="school_logo"
                                            accept="image/*">
                                        @if($setting->school_logo_path)
                                            <div class="mt-2">
                                                <img src="{{ asset($setting->school_logo_path) }}" alt="Current Logo"
                                                    style="max-height: 50px;">
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <label for="login_background" class="form-label">Login Page Background</label>
                                        <input class="form-control" type="file" id="login_background"
                                            name="login_background" accept="image/*">
                                        @if($setting->login_background_path)
                                            <div class="mt-2">
                                                <img src="{{ asset($setting->login_background_path) }}" alt="Current Background"
                                                    style="max-height: 100px;">
                                            </div>
                                        @endif
                                    </div>

                                    <h5 class="mt-4 mb-3 border-bottom pb-2">Attendance & Geofencing</h5>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="office_lat" class="form-label">Office Latitude</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="office_lat" name="office_lat"
                                                    value="{{ old('office_lat', $setting->office_lat) }}"
                                                    placeholder="e.g. 6.5244">
                                                <button class="btn btn-outline-secondary" type="button" id="get_location">
                                                    <i class="bi bi-geo-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="office_long" class="form-label">Office Longitude</label>
                                            <input type="text" class="form-control" id="office_long" name="office_long"
                                                value="{{ old('office_long', $setting->office_long) }}"
                                                placeholder="e.g. 3.3792">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="geo_range" class="form-label">Geofencing Radius (Meters)</label>
                                            <input type="number" class="form-control" id="geo_range" name="geo_range"
                                                value="{{ old('geo_range', $setting->geo_range ?? 500) }}" min="1">
                                            <div class="form-text">Allowed distance from office for check-in.</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="late_time" class="form-label">Late Arrival Threshold</label>
                                            <input type="time" class="form-control" id="late_time" name="late_time"
                                                value="{{ old('late_time', $setting->late_time ? \Carbon\Carbon::parse($setting->late_time)->format('H:i') : '08:00') }}">
                                            <div class="form-text">Staff checking in after this time will be marked "Late".
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 text-end">
                                        <button type="submit" class="btn btn-primary">Save Settings</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>

    <script>
        document.getElementById('get_location')?.addEventListener('click', function () {
            if (navigator.geolocation) {
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        document.getElementById('office_lat').value = position.coords.latitude.toFixed(8);
                        document.getElementById('office_long').value = position.coords.longitude.toFixed(8);
                        this.innerHTML = '<i class="bi bi-geo-alt"></i>';
                    },
                    (error) => {
                        alert("Error getting location: " + error.message);
                        this.innerHTML = '<i class="bi bi-geo-alt"></i>';
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        });
    </script>
@endsection
