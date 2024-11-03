<div class="tab-pane fade @if ($tab == 'password' || session('status') == 'password-updated') show active @endif" id="password" role="tabpanel">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Change Password</h5>
            <form method="post" action="{{ route('user.password_update', $user->id) }}">
                @csrf
                @method('POST')

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" placeholder="Enter your new password">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                        id="password_confirmation" name="password_confirmation" placeholder="Enter your password again">
                    @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Update password</button>
            </form>

        </div>
    </div>
</div>
