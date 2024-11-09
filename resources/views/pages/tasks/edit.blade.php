@extends('layouts.app')

@section('title', 'Edit Task')

@section('styles')

@endsection

@section('scripts')


    <script>
        $(document).ready(function() {

        });
    </script>
@endsection

@section('content')
    <h1 class="h3 mb-3">Edit Task</h1>

    <div class="row">

        <div class="col-md-8 col-xl-9">
            <div class="tab-content">

                <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Task Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Task Title</label>
                        <input type="text" name="title" id="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $task->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Task Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Task Description</label>
                        <textarea name="description" id="description" rows="4"
                            class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $task->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Assign User -->
                    <div class="form-group">
                        <label for="assigned_to" class="form-label">Assign User</label>
                        <select name="assigned_to" id="assigned_to"
                            class="form-control select2 @error('assigned_to') is-invalid @enderror" required>
                            <option value="">Select User</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Task Status -->
                    <div class="form-group">
                        <label for="status" class="form-label">Task Status</label>
                        <select name="status" id="status"
                            class="form-control select2 @error('status') is-invalid @enderror" required>
                            <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>
                                Pending</option>
                            <option value="in_progress"
                                {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>
                                Completed</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </form>
            </div>
        </div>
    </div>
@endsection
