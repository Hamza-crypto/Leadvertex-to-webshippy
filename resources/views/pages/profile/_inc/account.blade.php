@php
    $name = old('name', $user->name ?? '');
    $p_status = session('status');
@endphp

<div class="tab-pane fade  @if ($tab == 'account') show active @endif " id="account" role="tabpanel">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Basic Info</h5>
        </div>
        <div class="card-body">

            <form method="post" action="{{ route('profile.account') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" placeholder="Enter your name" value="{{ old('name', $user->name) }}">
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" placeholder="Enter your email" value="{{ old('email', $user->email) }}">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update account</button>
            </form>
        </div>
    </div>

    @if ($user->role == 'customer')
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Card Category</h5>
            </div>
            <div class="card-body">
                {{ $user_category }}
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Availability Status</h5>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('available.status') }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <div>
                            <label class="switch">
                                <input type="checkbox" name="status" @if ($status == '1') checked @endif>
                                <span class="slider round"></span>
                            </label>
                        </div>

                    </div>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>
        </div>
    @endif
</div>
