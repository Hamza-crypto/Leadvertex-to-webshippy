@extends('layouts.app')

@section('title', __('Add User'))
@section('scripts')

@endsection
@section('content')
    <h1 class="h3 mb-3">{{ __('Add User') }}</h1>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form method="post" action="{{ route('users.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input class="form-control form-control-lg mb-3" type="text" name="name" placeholder="Name"
                                required />
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input class="form-control form-control-lg mb-3" type="email" name="email"
                                placeholder="Email" required />
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input class="form-control form-control-lg mb-3" type="password" name="password"
                                placeholder="Password" required />
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input class="form-control form-control-lg mb-3" type="password" name="password_confirmation"
                                placeholder="Confirm Password" required />
                        </div>

                        <div class="form-group">
                            <label for="role">{{ __('Role') }}</label>
                            <select id="role" class="form-control select2 @error('role') is-invalid @enderror"
                                name="role" required>
                                @foreach (\App\Models\User::getRoles() as $key => $value)
                                    <option value="{{ $key }}">{{ ucfirst(str_replace('_', ' ', $value)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" class="form-control select2 @error('status') is-invalid @enderror"
                                name="status" required>
                                <option value="0">Pending</option>
                                <option value="1">Active</option>
                                <option value="2">Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-lg btn-primary">{{ __('Add User') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
