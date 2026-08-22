@csrf
@if (isset($visitPlan))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" @selected((string) old('user_id', $visitPlan->user_id ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Dealer <span class="text-danger">*</span></label>
        <select name="dealer_id" class="form-select @error('dealer_id') is-invalid @enderror" required>
            <option value="">— Select Dealer —</option>
            @foreach ($dealers as $dealer)
                <option value="{{ $dealer->id }}" @selected((string) old('dealer_id', $visitPlan->dealer_id ?? '') === (string) $dealer->id)>{{ $dealer->name }} ({{ $dealer->dealer_code }})</option>
            @endforeach
        </select>
        @error('dealer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Planned Date <span class="text-danger">*</span></label>
        <input type="date" name="planned_date" class="form-control @error('planned_date') is-invalid @enderror"
               value="{{ old('planned_date', isset($visitPlan) ? $visitPlan->planned_date->toDateString() : '') }}" required>
        @error('planned_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected((string) old('status', $visitPlan->status->value ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $visitPlan->notes ?? '') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($visitPlan) ? 'Update Visit Plan' : 'Plan Visit' }}</button>
    <a href="{{ route('visit-plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
