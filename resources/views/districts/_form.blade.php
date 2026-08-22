@csrf
@if (isset($district))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Division <span class="text-danger">*</span></label>
        <select name="division_id" class="form-select @error('division_id') is-invalid @enderror" required>
            <option value="">— Select Division —</option>
            @foreach ($divisions as $divisionOption)
                <option value="{{ $divisionOption->id }}" @selected((string) old('division_id', $district->division_id ?? '') === (string) $divisionOption->id)>
                    {{ $divisionOption->name }}
                </option>
            @endforeach
        </select>
        @error('division_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $district->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name (Bangla)</label>
        <input type="text" name="name_bn" class="form-control @error('name_bn') is-invalid @enderror"
               value="{{ old('name_bn', $district->name_bn ?? '') }}">
        @error('name_bn') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $district->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($district) ? 'Update District' : 'Create District' }}</button>
    <a href="{{ route('districts.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
