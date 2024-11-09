@extends('layouts.app')

@section('title', __('Tasks '))

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#tasks-table').DataTable();
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

    @php
        $roleBadges = [
            'pending' => 'badge-warning',
            'in_progress' => 'badge-primary',
            'completed' => 'badge-success',
        ];
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <table class="table table-striped" id="tasks-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                @can('delete', $tasks[0])
                                    <th>Assigned To</th>
                                @endcan
                                <th>Status</th>
                                <th>Created At</th>
                                @can('delete', $tasks[0])
                                    <th>Actions</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>{{ $loop->index + 1 }}</td>
                                    <td>{{ $task->title }}</td>
                                    @can('delete', $tasks[0])
                                        <td>{{ $task->assignedUser->name }}</td>
                                    @endcan
                                    <td>
                                        <div class="form-group">
                                            <form action="{{ route('tasks.updateStatus', $task->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <select name="status" class="form-control" onchange="this.form.submit()">
                                                    <option value="pending"
                                                        {{ $task->status == 'pending' ? 'selected' : '' }}>
                                                        Pending</option>
                                                    <option value="in_progress"
                                                        {{ $task->status == 'in_progress' ? 'selected' : '' }}>In Progress
                                                    </option>
                                                    <option value="completed"
                                                        {{ $task->status == 'completed' ? 'selected' : '' }}>Completed
                                                    </option>
                                                </select>

                                            </form>
                                        </div>
                                    </td>
                                    <td>{{ $task->created_at->diffForHumans() }}</td>

                                    @can('delete', $task)
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
                                    @endcan

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
