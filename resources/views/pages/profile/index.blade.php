@extends('layouts.app')

@section('title', 'Profile')

@section('styles')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

        });
    </script>
@endsection
@php
    $role = Auth()->user()->role;
@endphp

@section('content')
    <h1 class="h3 mb-3">Profile</h1>
    <div class="row">

        <div class="col-md-4 col-xl-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Details</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">

                        <img alt="{{ $user->name }}"
                            src="https://ui-avatars.com/api/?name={{ $user->name }}&background=293042&color=ffffff"
                            class="img-fluid rounded-circle mb-2" width="128" height="128">


                        <h5 class="card-title mb-0">{{ $user->name }}</h5>
                        <div class="text-muted mb-2">{{ $user->email }}</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Settings</h5>
                </div>
                <div class="list-group list-group-flush" role="tablist">
                    <a class="list-group-item list-group-item-action @if ($tab == 'account') active @endif"
                        data-toggle="list" href="#account" role="tab">
                        Account
                    </a>



                    <a class="list-group-item list-group-item-action @if (session('status') == 'password-updated') active @endif"
                        data-toggle="list" href="#password" role="tab">
                        Change Password
                    </a>


                </div>
            </div>
        </div>
        <div class="col-md-8 col-xl-9">
            <div class="tab-content">
                @include('pages.profile._inc.account')
                @include('pages.profile._inc.password')
            </div>
        </div>
    </div>

    <style>
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }
    </style>
@endsection
