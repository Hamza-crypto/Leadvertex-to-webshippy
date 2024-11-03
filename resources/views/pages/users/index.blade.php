@extends('layouts.app')

@section('title', __('Users'))

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#users-table').DataTable();
        });

        function confirmSubmission(event, userName) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: `Are you sure you want to delete user ${userName}?`,
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
    <h1 class="h3 mb-3">{{ __('All Users') }}</h1>

    @php
        $roleBadges = [
            'admin' => 'badge-success',
            'operational_manager' => 'badge-primary',
            'marketing_manager' => 'badge-info',
        ];
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">

                    <table id="users-table" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>{{ 'ID' }}</th>
                                <th>{{ 'Name' }}</th>
                                <th>{{ 'Email' }}</th>
                                <th>{{ 'Role' }}</th>
                                <th>{{ 'Created at' }}</th>
                                <th>{{ 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>

                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $user->name }}
                                    </td>
                                    <td>{{ $user->email }}</td>

                                    <td>

                                        <span class="badge {{ $roleBadges[$user->role] ?? 'badge-secondary' }}">
                                            {{ \App\Models\User::getRoles()[$user->role] ?? 'Unknown Role' }}
                                        </span>
                                    </td>


                                    <td data-sort="{{ strtotime($user->created_at) }}">
                                        {{ $user->created_at->diffForHumans() }}</td>
                                    <td class="table-action">

                                        <a href="{{ route('users.edit', $user->id) }}" class="btn"
                                            style="display: inline">
                                            <i class="fa fa-edit text-info"></i>
                                        </a>

                                        @if (auth()->user()->id != $user->id)
                                            <form method="post" action="{{ route('users.destroy', $user->id) }}"
                                                onsubmit="return confirmSubmission(event, '{{ $user->name }}')"
                                                style="display: inline">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn text-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>

                                            @canImpersonate($guard = null)
                                            <a href="{{ route('impersonate', $user->id) }}" class="btn"
                                                style="display: inline">
                                                <i class="fa fa-user-cog"></i>
                                            </a>
                                            @endCanImpersonate
                                        @endif



                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
