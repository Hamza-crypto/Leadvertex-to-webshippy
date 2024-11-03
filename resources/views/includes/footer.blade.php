@php
    $role = Auth()->user()->role;
@endphp

<footer class="footer">
    <div class="container-fluid">
        <div class="row text-muted">
            <div class="col-6 text-left">
            </div>
            <div class="col-6 text-right">
                <p class="mb-0">
                    &copy; {{ date('Y') }} - <a href="{{ env('APP_URL') }}"
                        class="text-muted">{{ env('APP_NAME') }}</a>
                </p>
            </div>
        </div>
    </div>
</footer>
