@csrf
@if (isset($depot))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               value="{{ old('code', $depot->code ?? '') }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $depot->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Territory</label>
        <select name="territory_id" class="form-select @error('territory_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach ($territories as $territory)
                <option value="{{ $territory->id }}" @selected((string) old('territory_id', $depot->territory_id ?? '') === (string) $territory->id)>{{ $territory->name }}</option>
            @endforeach
        </select>
        @error('territory_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
               value="{{ old('address', $depot->address ?? '') }}">
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    @if (isset($depot) && $depot->tally_guid)
        <div class="col-12">
            <label class="form-label">Tally GUID</label>
            <input type="text" class="form-control" value="{{ $depot->tally_guid }}" disabled>
            <div class="form-text">Filled in automatically once this depot syncs with its matching Tally Godown.</div>
        </div>
    @endif

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $depot->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($depot) ? 'Update Depot' : 'Create Depot' }}</button>
    <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
