@csrf
@if (isset($serviceCenter))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $serviceCenter->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
               value="{{ old('phone', $serviceCenter->phone ?? '') }}">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $serviceCenter->address ?? '') }}</textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Latitude</label>
        <input type="number" step="0.0000001" name="lat" class="form-control @error('lat') is-invalid @enderror"
               value="{{ old('lat', $serviceCenter->lat ?? '') }}">
        @error('lat') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Longitude</label>
        <input type="number" step="0.0000001" name="lng" class="form-control @error('lng') is-invalid @enderror"
               value="{{ old('lng', $serviceCenter->lng ?? '') }}">
        @error('lng') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $serviceCenter->is_active ?? true))>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($serviceCenter) ? 'Update Service Center' : 'Create Service Center' }}</button>
    <a href="{{ route('service-centers.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
