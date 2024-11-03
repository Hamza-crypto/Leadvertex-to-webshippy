@extends('layouts.app')

@section('title', 'Profile')

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.0.0/magnific-popup.min.css" />
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.0.0/jquery.magnific-popup.min.js"></script>

    <script>
        $(document).ready(function() {

        });
    </script>
@endsection

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



                    <a class="list-group-item list-group-item-action @if ($tab == 'password' || session('status') == 'password-updated') active @endif"
                        data-toggle="list" href="#password" role="tab">
                        Change Password
                    </a>

                </div>
            </div>
        </div>
        <div class="col-md-8 col-xl-9">
            <div class="tab-content">

                @include('pages.users._inc.account')
                @include('pages.users._inc.password_admin')
            </div>
        </div>
    </div>
@endsection
