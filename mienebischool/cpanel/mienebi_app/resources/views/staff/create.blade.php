@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-start">
            @include('layouts.left-menu')
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-10">
                <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3">
                    <h1 class="h3 mb-0 text-gray-800">Add New Staff</h1>
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Personnel Details</h6>
                    </div>
                    <div class="card-body">
                        @if ((is_array($errors) && count($errors) > 0) || (is_object($errors) && $errors->any()))
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ((is_array($errors) ? $errors : $errors->all()) as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('staff.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="first_name" class="form-label">First Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="first_name"
                                        value="{{ old('first_name') }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="last_name" class="form-label">Last Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="last_name" value="{{ old('last_name') }}"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" name="role" required>
                                        <option value="">-- Select Role --</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator
                                        </option>
                                        <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher
                                        </option>
                                        <option value="accountant" {{ old('role') == 'accountant' ? 'selected' : '' }}>
                                            Accountant
                                        </option>
                                        <option value="librarian" {{ old('role') == 'librarian' ? 'selected' : '' }}>Librarian
                                        </option>
                                        <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>General Staff
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nationality" class="form-label">Nationality <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nationality"
                                        value="{{ old('nationality') }}" required placeholder="e.g. Nigerian">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">-- Select Gender --</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">Address <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="address" value="{{ old('address') }}"
                                        required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label for="address2" class="form-label">Address 2</label>
                                    <input type="text" class="form-control" name="address2" value="{{ old('address2') }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="city" value="{{ old('city') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="zip" class="form-label">Zip Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="zip" value="{{ old('zip') }}" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Save Personnel Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @include('layouts.footer')
            </div>
        </div>
    </div>
@endsection