@extends('layouts.app')

@section('title', __('Edit Doctor Visit'))
@section('scripts')

@endsection
@section('content')
    <h1 class="h3 mb-3">Edit Doctor Visit</h1>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="post" action="{{ route('doctor-visits.update', $doctorVisit->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Hospital -->
                        <div class="mb-3">
                            <label for="hospital" class="form-label">Hospital</label>
                            <input type="text" name="hospital" id="hospital"
                                class="form-control @error('hospital') is-invalid @enderror"
                                value="{{ old('hospital', $doctorVisit->hospital) }}" required>
                            @error('hospital')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Type -->
                        <div class="form-group">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" id="type"
                                class="form-control select2 @error('type') is-invalid @enderror" required>
                                <option value="">Select Type</option>
                                <option value="government" {{ old('type', $doctorVisit->type) == 'government' ? 'selected' : '' }}>Government</option>
                                <option value="private" {{ old('type', $doctorVisit->type) == 'private' ? 'selected' : '' }}>Private</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Potential -->
                        <div class="form-group">
                            <label for="potential" class="form-label">Potential</label>
                            <select name="potential" id="potential"
                                class="form-control select2 @error('potential') is-invalid @enderror" required>
                                <option value="">Select Potential</option>
                                <option value="low" {{ old('potential', $doctorVisit->potential) == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('potential', $doctorVisit->potential) == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('potential', $doctorVisit->potential) == 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            @error('potential')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status"
                                class="form-control select2 @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                <option value="open" {{ old('status', $doctorVisit->status) == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="closed" {{ old('status', $doctorVisit->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Chain -->
                        <div class="mb-3">
                            <label for="chain" class="form-label">Chain</label>
                            <input type="text" name="chain" id="chain"
                                class="form-control @error('chain') is-invalid @enderror"
                                value="{{ old('chain', $doctorVisit->chain) }}" required>
                            @error('chain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" id="address"
                                class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $doctorVisit->address) }}" required>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" name="city" id="city"
                                class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city', $doctorVisit->city) }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contact Person -->
                        <div class="mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" id="contact_person"
                                class="form-control @error('contact_person') is-invalid @enderror"
                                value="{{ old('contact_person', $doctorVisit->contact_person) }}" required>
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contact Position -->
                        <div class="mb-3">
                            <label for="contact_position" class="form-label">Contact Position</label>
                            <input type="text" name="contact_position" id="contact_position"
                                class="form-control @error('contact_position') is-invalid @enderror"
                                value="{{ old('contact_position', $doctorVisit->contact_position) }}" required>
                            @error('contact_position')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number"
                                class="form-control @error('phone_number') is-invalid @enderror"
                                value="{{ old('phone_number', $doctorVisit->phone_number) }}" required>
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $doctorVisit->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Responsible -->
                        <div class="mb-3">
                            <label for="responsible" class="form-label">Responsible</label>
                            <input type="text" name="responsible" id="responsible"
                                class="form-control @error('responsible') is-invalid @enderror"
                                value="{{ old('responsible', $doctorVisit->responsible) }}" required>
                            @error('responsible')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Visits -->
                        <div class="mb-3">
                            <label for="visits" class="form-label">Visits</label>
                            <textarea name="visits" id="visits" rows="4"
                                class="form-control @error('visits') is-invalid @enderror" required>{{ old('visits', $doctorVisit->visits) }}</textarea>
                            @error('visits')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Update Doctor Visit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
