@csrf
@if (isset($leaveType))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
               placeholder="e.g. Casual Leave" value="{{ old('name', $leaveType->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
               placeholder="e.g. CL" value="{{ old('code', $leaveType->code ?? '') }}" required>
        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Annual Quota (days) <span class="text-danger">*</span></label>
        <input type="number" name="annual_quota" min="0" max="365" class="form-control @error('annual_quota') is-invalid @enderror"
               value="{{ old('annual_quota', $leaveType->annual_quota ?? 0) }}" required>
        @error('annual_quota') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">
            The default entitlement given to someone when their balance for this type is first set up.
            Changing it later does not alter entitlements already granted.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $leaveType->description ?? '') }}</textarea>
        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="is_paid" value="0">
            <input type="checkbox" class="form-check-input" id="is_paid" name="is_paid" value="1" @checked(old('is_paid', $leaveType->is_paid ?? true))>
            <label class="form-check-label" for="is_paid">Paid leave</label>
        </div>
        <div class="form-text">Unpaid leave is still requested and approved here; the flag is for payroll.</div>
    </div>

    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">
            <input type="checkbox" class="form-check-input" id="status" name="status" value="1" @checked(old('status', $leaveType->status ?? true))>
            <label class="form-check-label" for="status">Active</label>
        </div>
        <div class="form-text">Inactive types can no longer be requested; existing requests keep their type.</div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($leaveType) ? 'Update Leave Type' : 'Create Leave Type' }}</button>
    <a href="{{ route('leave-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
