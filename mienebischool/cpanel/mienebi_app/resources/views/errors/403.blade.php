@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">{{ __('403 Forbidden') }}</div>

                    <div class="card-body text-center">
                        <h1 class="display-4 text-danger"><i class="bi bi-shield-lock-fill"></i></h1>
                        <h3>{{ __($exception->getMessage() ?: 'Forbidden') }}</h3>
                        <p class="lead">You do not have permission to access this resource.</p>



                        <a href="{{ url('/') }}" class="btn btn-primary mt-3"><i class="bi bi-house-door"></i>
                            {{ __('Go Home') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
