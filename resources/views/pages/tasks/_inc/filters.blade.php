<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="filter-form">
                    <input type="hidden" class="d-none" name="filter" value="true" hidden>
                    <div class="row">

                        <!-- User Filter -->
                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="user"> User </label>
                                <select name="user" id="user"
                                    class="form-control form-select custom-select select2" data-toggle="select2">
                                    <option value="-100"> Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-sm">
                            <div class="form-group">
                                <label class="form-label" for="status"> Status </label>
                                <select name="status" id="status"
                                    class="form-control form-select custom-select select2" data-toggle="select2">
                                    <option value="-100"> Select Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-sm mt-4">
                            <button type="submit" class="btn btn-sm btn-primary mt-2">Apply</button>
                            <button type="button" class="btn btn-sm btn-secondary mt-2"
                                id="clear-button">Clear</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
