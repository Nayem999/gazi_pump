@csrf
@if (isset($thana))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Division <span class="text-danger">*</span></label>
        <select id="division_id" class="form-select">
            <option value="">— Select Division —</option>
            @foreach ($divisions as $divisionOption)
                <option value="{{ $divisionOption->id }}" @selected((string) ($currentDivisionId ?? old('division_id', '')) === (string) $divisionOption->id)>
                    {{ $divisionOption->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Used to narrow the District list below — only the District is saved.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">District <span class="text-danger">*</span></label>
        <select name="district_id" id="district_id" class="form-select @error('district_id') is-invalid @enderror"
                data-initial-value="{{ old('district_id', $thana->district_id ?? '') }}"
                @if (! isset($thana) && ! old('district_id')) disabled @endif required>
            @if (isset($thana))
                <option value="{{ $thana->district_id }}" selected>{{ $thana->district->name }}</option>
            @else
                <option value="">— Select District —</option>
            @endif
        </select>
        @error('district_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $thana->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name (Bangla)</label>
        <input type="text" name="name_bn" class="form-control @error('name_bn') is-invalid @enderror"
               value="{{ old('name_bn', $thana->name_bn ?? '') }}">
        @error('name_bn') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $thana->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($thana) ? 'Update Thana' : 'Create Thana' }}</button>
    <a href="{{ route('thanas.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
