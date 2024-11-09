@php
    $role = auth()->user()->role;

    $statuses = [
        'pending' => '#faeddb', // Yellow
        'in_progress' => '#d9e6fb', // Blue
        'completed' => '#dbf2e3', // Green
    ];
@endphp

@extends('layouts.app')

@section('title', __('Tasks '))

@section('scripts')
    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);

            const user = urlParams.get('user');
            const status = urlParams.get('status');


            if (user && user !== '-100') {
                $('#user').val(user).trigger('change');
            }

            if (status && status !== '-100') {
                $('#status').val(status).trigger('change');
            }

            $('#tasks-table').DataTable();


        });

        $('#clear-button').click(function() {
            $('#user').val('-100').trigger('change');
            $('#status').val('-100').trigger('change');

            $('#filter-form').submit();
        });

        function confirmSubmission(event, taskTitle) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: `Are you sure you want to delete the task ${taskTitle}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit(); // Submit the form if confirmed
                }
            });
        }
    </script>
@endsection

@section('content')
    <h1 class="h3 mb-3">{{ __('All Tasks') }}</h1>

    @if ($role == 'admin')
        @include('pages.tasks._inc.filters')
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="tasks-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                @if ($role == 'admin')
                                    <th>Assigned To</th>
                                @endif
                                <th>Status</th>
                                <th>Created At</th>
                                @if ($role == 'admin')
                                    <th>Actions</th>
                                @endif

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $task->title }}</td>
                                    @if ($role == 'admin')
                                        <td>{{ $task->assignedUser->name }}</td>
                                    @endif
                                    <td>
                                        <div class="form-group">
                                            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <!-- Status Dropdown with Background Color Change -->
                                                <select name="status" class="form-control mt-2"
                                                    onchange="this.form.submit()"
                                                    style="background-color: {{ $statuses[$task->status] ?? '#ffffff' }};">
                                                    @foreach ($statuses as $status => $color)
                                                        <option value="{{ $status }}" data-color="{{ $color }}"
                                                            style="background-color: {{ $color }}; color: #black; "
                                                            {{ $task->status == $status ? 'selected' : '' }}>
                                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                            </form>
                                        </div>
                                    </td>
                                    <td>{{ $task->created_at->diffForHumans() }}</td>

                                    @if ($role == 'admin')
                                        <td class="table-action">

                                            <a href="{{ route('tasks.edit', $task->id) }}" class="btn"
                                                style="display: inline">
                                                <i class="fa fa-edit text-info"></i>
                                            </a>

                                            <!-- Delete Task (Only Admins) -->

                                            <form method="post" action="{{ route('tasks.destroy', $task->id) }}"
                                                onsubmit="return confirmSubmission(event, '{{ $task->title }}')"
                                                style="display: inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn text-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No tasks yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
