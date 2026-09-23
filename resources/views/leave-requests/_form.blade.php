@csrf
@if (isset($leaveRequest))
    @method('PUT')
@endif

<div class="row g-3">
    @if (isset($users) && $users->count() > 1)
        {{-- Only shown to someone who may file for others; the request
             class refuses a user_id from anyone without approve rights. --}}
        <div class="col-md-6">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id', auth()->id()) == $user->id)>
                        {{ $user->name }} @if ($user->employee_id) ({{ $user->employee_id }}) @endif
                    </option>
                @endforeach
            </select>
            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @endif

    <div class="col-md-6">
        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
        <select name="leave_type_id" class="form-select @error('leave_type_id') is-invalid @enderror" required>
            <option value="">Select a type</option>
            @foreach ($leaveTypes as $type)
                <option value="{{ $type->id }}" @selected(old('leave_type_id', $leaveRequest->leave_type_id ?? '') == $type->id)>
                    {{ $type->name }} ({{ $type->annual_quota }} day(s) a year)
                </option>
            @endforeach
        </select>
        @error('leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">From <span class="text-danger">*</span></label>
        <input type="date" name="from_date" class="form-control @error('from_date') is-invalid @enderror"
               value="{{ old('from_date', isset($leaveRequest) ? $leaveRequest->from_date->toDateString() : '') }}" required>
        @error('from_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">To <span class="text-danger">*</span></label>
        <input type="date" name="to_date" class="form-control @error('to_date') is-invalid @enderror"
               value="{{ old('to_date', isset($leaveRequest) ? $leaveRequest->to_date->toDateString() : '') }}" required>
        @error('to_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <div class="form-text">Weekends and holidays in this range are not counted as leave.</div>
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_half_day" value="0">
            <input type="checkbox" class="form-check-input" id="is_half_day" name="is_half_day" value="1"
                   @checked(old('is_half_day', $leaveRequest->is_half_day ?? false))>
            <label class="form-check-label" for="is_half_day">Half day</label>
        </div>
        <div class="form-text">A half day applies to a single date, so From and To must match. It counts as 0.5 days.</div>
        @error('is_half_day') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Reason <span class="text-danger">*</span></label>
        <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror"
                  placeholder="Why you need this time off" required>{{ old('reason', $leaveRequest->reason ?? '') }}</textarea>
        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="ti ti-check me-1"></i>{{ isset($leaveRequest) ? 'Update Request' : 'Submit Leave Application' }}
    </button>
    <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
