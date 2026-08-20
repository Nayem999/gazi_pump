@csrf
@if (isset($attendance))
    @method('PUT')
@endif

<div class="row g-3">
    @if (! isset($attendance))
        <div class="col-md-6">
            <label class="form-label">Employee <span class="text-danger">*</span></label>
            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                <option value="">— Select Employee —</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>{{ $user->name }} ({{ $user->employee_id }})</option>
                @endforeach
            </select>
            @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @else
        <div class="col-md-6">
            <label class="form-label">Employee</label>
            <input type="text" class="form-control" value="{{ $attendance->user->name }} ({{ $attendance->user->employee_id }})" disabled>
        </div>
    @endif

    <div class="col-md-6">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
               value="{{ old('date', isset($attendance) ? $attendance->date->toDateString() : '') }}" required>
        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected((string) old('status', $attendance->status->value ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Check In Time</label>
        <input type="datetime-local" name="check_in_at" class="form-control @error('check_in_at') is-invalid @enderror"
               value="{{ old('check_in_at', isset($attendance) ? $attendance->check_in_at?->format('Y-m-d\TH:i') : '') }}">
        @error('check_in_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Check Out Time</label>
        <input type="datetime-local" name="check_out_at" class="form-control @error('check_out_at') is-invalid @enderror"
               value="{{ old('check_out_at', isset($attendance) ? $attendance->check_out_at?->format('Y-m-d\TH:i') : '') }}">
        @error('check_out_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $attendance->remarks ?? '') }}</textarea>
        <div class="form-text">Use this for manual corrections, e.g. approved leave or a missed check-in/out.</div>
        @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($attendance) ? 'Update Attendance' : 'Record Attendance' }}</button>
    <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
