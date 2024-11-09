@extends('layouts.app')

@section('title', __('Add Task'))
@section('scripts')

@endsection
@section('content')
    <h1 class="h3 mb-3">Create New Task</h1>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <form action="{{ route('tasks.store') }}" method="POST">
                        @csrf

                        <!-- Task Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Task Title</label>
                            <input type="text" name="title" id="title"
                                class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}"
                                required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Task Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Task Description</label>
                            <textarea name="description" id="description" rows="4"
                                class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
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
                                        {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}</option>
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
                                class="form-control select2 @error('status') is-invalid @enderror" required
                                data-toggle="select2">
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Create Task</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
