@csrf
@if (isset($holiday))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
               value="{{ old('date', isset($holiday) ? $holiday->date->toDateString() : '') }}" required>
        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               placeholder="e.g. Independence Day" value="{{ old('name', $holiday->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $holiday->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $holiday->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
        <div class="form-text">Inactive holidays are kept on record but no longer skipped when calculating attendance.</div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($holiday) ? 'Update Holiday' : 'Create Holiday' }}</button>
    <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
