<h1 class="h3 mb-3">Add New File </h1>

<div class="row">

    <div class="col">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('status.bulk.update') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <div class="mb-3">
                            <label class="form-label w-100">Copart</label>
                            <input type="file" name="csv_file" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-lg btn-primary add-btn">Add New File
                        </button>
                    </div>


                </form>
            </div>
        </div>
    </div>

</div>
