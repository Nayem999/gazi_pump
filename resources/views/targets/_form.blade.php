@csrf
@if (isset($target))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" @selected((string) old('user_id', $target->user_id ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Month <span class="text-danger">*</span></label>
        <select name="month" class="form-select @error('month') is-invalid @enderror" required>
            @foreach (range(1, 12) as $month)
                <option value="{{ $month }}" @selected((string) old('month', $target->month ?? now()->month) === (string) $month)>{{ \Illuminate\Support\Carbon::create(2000, $month, 1)->format('F') }}</option>
            @endforeach
        </select>
        @error('month') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Year <span class="text-danger">*</span></label>
        <input type="number" name="year" class="form-control @error('year') is-invalid @enderror"
               value="{{ old('year', $target->year ?? now()->year) }}" required>
        @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Sales Value Target <span class="text-danger">*</span></label>
        <input type="number" min="1" step="0.01" name="sales_value_target" class="form-control @error('sales_value_target') is-invalid @enderror"
               value="{{ old('sales_value_target', $target->sales_value_target ?? '') }}" required>
        @error('sales_value_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Collection Target <span class="text-danger">*</span></label>
        <input type="number" min="1" step="0.01" name="collection_target" class="form-control @error('collection_target') is-invalid @enderror"
               value="{{ old('collection_target', $target->collection_target ?? '') }}" required>
        @error('collection_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Quantity Target (units) <span class="text-danger">*</span></label>
        <input type="number" min="1" step="1" name="quantity_target" class="form-control @error('quantity_target') is-invalid @enderror"
               value="{{ old('quantity_target', $target->quantity_target ?? '') }}" required>
        @error('quantity_target') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $target->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($target) ? 'Update Target' : 'Assign Target' }}</button>
    <a href="{{ route('targets.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
