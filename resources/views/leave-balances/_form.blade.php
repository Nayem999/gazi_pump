@csrf
@if (isset($leaveBalance))
    @method('PUT')
@endif

<div class="row g-3">
    @if (isset($leaveBalance))
        {{-- Who and which year are an entitlement's identity: moving one
             would silently re-grant a person's days to someone else, so
             they are shown read-only here. Delete and re-add instead,
             which leaves both acts on the audit trail. --}}
        <div class="col-md-6">
            <label class="form-label">Employee</label>
            <input type="text" class="form-control" value="{{ $leaveBalance->user?->name }} ({{ $leaveBalance->user?->employee_id }})" disabled>
        </div>
        <div class="col-md-3">
            <label class="form-label">Leave Type</label>
            <input type="text" class="form-control" value="{{ $leaveBalance->leaveType?->name }}" disabled>
        </div>
        <div class="col-md-3">
            <label class="form-label">Year</label>
            <input type="text" class="form-control" value="{{ $leaveBalance->year }}" disabled>
        </div>
    @else
        <div class="col-md-6">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                <option value="">Select an employee</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                        {{ $user->name }} @if ($user->employee_id) ({{ $user->employee_id }}) @endif
                    </option>
                @endforeach
            </select>
            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
            <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
                <option value="">Select a type</option>
                @foreach ($leaveTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>
                        {{ $type->name }} ({{ $type->annual_quota }} default)
                    </option>
                @endforeach
            </select>
            @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-3">
            <label class="form-label">Year <span class="text-danger">*</span></label>
            <input type="number" name="year" min="2000" max="2100" class="form-control @error('year') is-invalid @enderror"
                   value="{{ old('year', $year ?? now()->format('Y')) }}" required>
            @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <div class="alert alert-light border small mb-0">
                Saving over an entitlement that already exists for this employee, type and year updates it
                &mdash; including one that was deleted, which is restored rather than duplicated.
            </div>
        </div>
    @endif

    <div class="col-md-4">
        <label class="form-label">Entitled Days <span class="text-danger">*</span></label>
        <input type="number" name="entitled_days" step="0.5" min="0" max="365"
               class="form-control @error('entitled_days') is-invalid @enderror"
               value="{{ old('entitled_days', $leaveBalance->entitled_days ?? 0) }}" required>
        @error('entitled_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">The grant for this year. Half days are allowed.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Carried Forward</label>
        <input type="number" name="carried_forward_days" step="0.5" min="0" max="365"
               class="form-control @error('carried_forward_days') is-invalid @enderror"
               value="{{ old('carried_forward_days', $leaveBalance->carried_forward_days ?? 0) }}">
        @error('carried_forward_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Unused days brought in from last year, kept separate so both stay legible.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Remarks</label>
        <input type="text" name="remarks" maxlength="500" class="form-control @error('remarks') is-invalid @enderror"
               placeholder="e.g. pro-rated, joined in March" value="{{ old('remarks', $leaveBalance->remarks ?? '') }}">
        @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-text">
            Days <strong>taken</strong> are not set here. They are counted from approved leave requests, so the
            remaining balance always agrees with the request list.
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="ti ti-check me-1"></i>{{ isset($leaveBalance) ? 'Update Entitlement' : 'Save Entitlement' }}
    </button>
    <a href="{{ route('leave-balances.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
