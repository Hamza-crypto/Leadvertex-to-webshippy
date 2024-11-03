@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <div class="text-center mt-4">
        <h1 class="h2">Register</h1>
        <p class="lead">
            Start creating the best possible user experience for you customers.
        </p>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="m-sm-4">

                <form method="post" action="{{ route('register') }}">
                    @csrf

                    <x-input type="text" label="Name" placeholder="Enter your name"></x-input>

                    <x-input type="email" label="Email" placeholder="Enter your email"></x-input>

                    <x-input type="password" label="Password" placeholder="Enter your password"></x-input>

                    <x-input type="password" label="Confirm Password" name="password_confirmation" placeholder="Enter your password again"></x-input>

                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-lg btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
