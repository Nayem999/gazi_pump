@csrf
@if (isset($vehicle))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Registration Number <span class="text-danger">*</span></label>
        <input type="text" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror"
               value="{{ old('registration_number', $vehicle->registration_number ?? '') }}" required>
        @error('registration_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Type</label>
        <input type="text" name="type" class="form-control @error('type') is-invalid @enderror"
               value="{{ old('type', $vehicle->type ?? '') }}" placeholder="e.g. Truck, Van, Pickup">
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Capacity</label>
        <input type="text" name="capacity" class="form-control @error('capacity') is-invalid @enderror"
               value="{{ old('capacity', $vehicle->capacity ?? '') }}" placeholder="e.g. 2 Ton">
        @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $vehicle->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($vehicle) ? 'Update Vehicle' : 'Create Vehicle' }}</button>
    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
