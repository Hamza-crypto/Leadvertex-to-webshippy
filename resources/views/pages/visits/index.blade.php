@extends('layouts.app')

@section('title', __('Doctor Visits'))

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#visits-table').DataTable();
        });

        function confirmSubmission(event, visitHospital) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: `Are you sure you want to delete the visit for hospital: ${visitHospital}?`,
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
    <h1 class="h3 mb-3">{{ __('All Doctor Visits') }}</h1>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="visits-table" class="table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>{{ 'ID' }}</th>
                                <th>{{ 'Hospital' }}</th>
                                <th>{{ 'Type' }}</th>
                                <th>{{ 'City' }}</th>
                                <th>{{ 'Potential' }}</th>
                                <th>{{ 'Status' }}</th>
                                <th>{{ 'Contact Person' }}</th>
                                <th>{{ 'Created at' }}</th>
                                <th>{{ 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($doctorVisits as $visit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $visit->hospital }}</td>
                                    <td>
                                        <span class="badge badge-{{ $visit->type == 'government' ? 'success' : ($visit->type == 'private' ? 'danger' : 'info') }}">
                                            {{ ucfirst($visit->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $visit->city }}</td>
                                    <td>
                                        <span class="badge badge-{{ $visit->potential == 'high' ? 'danger' : ($visit->potential == 'medium' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($visit->potential) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $visit->status == 'open' ? 'success' : 'danger' }}">
                                            {{ ucfirst($visit->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $visit->contact_person }}</td>
                                    <td data-sort="{{ strtotime($visit->created_at) }}">
                                        {{ $visit->created_at->diffForHumans() }}
                                    </td>
                                    <td class="table-action">
                                        <a href="{{ route('doctor-visits.edit', $visit->id) }}" class="btn" style="display: inline">
                                            <i class="fa fa-edit text-info"></i>
                                        </a>
                                        <form method="post" action="{{ route('doctor-visits.destroy', $visit->id) }}"
                                            onsubmit="return confirmSubmission(event, '{{ $visit->hospital }}')"
                                            style="display: inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn text-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
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
