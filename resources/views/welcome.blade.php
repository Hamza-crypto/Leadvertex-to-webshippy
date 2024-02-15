@extends('layouts.app')

@section('title', 'Add File')

@section('scripts')
    <script></script>
@endsection

@section('content')

    <h1 class="h3 mb-3">Bulk Status Update</h1>

    <div class="row">

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('status.bulk.update') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <div class="mb-3">
                                <label class="form-label w-100">CSV File</label>
                                <input type="file" name="file" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label w-100">Select Status</label>
                            <div class="mb-3">
                                <label class="form-check">
                                    <input name="status" type="radio" class="form-check-input" value="paid"
                                        checked="">
                                    <span class="form-check-label">PAID</span>
                                </label>
                                <label class="form-check">
                                    <input name="status" type="radio" class="form-check-input" value="refused">
                                    <span class="form-check-label">RETURN</span>
                                </label>
                            </div>
                        </div>


                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-primary add-btn">Upload
                            </button>
                        </div>


                    </form>
                </div>
            </div>
        </div>

    </div>


@endsection
