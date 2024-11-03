@extends('layouts.auth')

@section('title', __('Reset password'))

@section('content')
    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="email" value="{{ request()->input('email') }}">
        <input type="hidden" name="token" value="{{ request()->route('token') }}">

        @if (session('status'))
            @section('alert')
                <script>
                    var notyf = new Notyf();

                    notyf.open({
                        'type': 'success',
                        'message': '{{ session('status') }}',
                        'duration': 60000,
                        'ripple': true,
                        'dismissible': true,
                        'position': {x: 'right', y: 'top'}
                    });
                </script>
            @endsection
        @endif

        <div class="mb-4">
            <h1 class="font-24-bold">{{ __('Reset') }}</h1>
            <h2 class="font-18-500 text-dark-secondary my-3">{{ __('Reset your password') }}</h2>
            <p>{{ __('Your new password must be different from previous used password.') }}</p>
        </div>

        <x-input type="password" label="{{ 'Password' }}" placeholder="{{ 'Enter your password' }}"></x-input>
        <x-input type="password" label="{{ 'Confirm Password' }}" name="password_confirmation" placeholder="{{ 'Enter your password again' }}"></x-input>

        <div class="row align-items-center">
            <div class="col-sm-12 mb-3 ">
                <button type="submit" class="btn btn-block btn-success btn-large">{{ __('Reset Password') }}</button>
            </div>
        </div>
    </form>
@endsection
