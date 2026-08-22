@csrf
@if (isset($visit))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" @selected((string) old('user_id', $visit->user_id ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Dealer <span class="text-danger">*</span></label>
        <select name="dealer_id" class="form-select @error('dealer_id') is-invalid @enderror" required>
            <option value="">— Select Dealer —</option>
            @foreach ($dealers as $dealer)
                <option value="{{ $dealer->id }}" @selected((string) old('dealer_id', $visit->dealer_id ?? '') === (string) $dealer->id)>{{ $dealer->name }} ({{ $dealer->dealer_code }})</option>
            @endforeach
        </select>
        @error('dealer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Check In <span class="text-danger">*</span></label>
        <input type="datetime-local" name="check_in_at" class="form-control @error('check_in_at') is-invalid @enderror"
               value="{{ old('check_in_at', isset($visit) ? $visit->check_in_at?->format('Y-m-d\TH:i') : '') }}" required>
        @error('check_in_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Check Out</label>
        <input type="datetime-local" name="check_out_at" class="form-control @error('check_out_at') is-invalid @enderror"
               value="{{ old('check_out_at', isset($visit) ? $visit->check_out_at?->format('Y-m-d\TH:i') : '') }}">
        @error('check_out_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Feedback</label>
        <textarea name="feedback" class="form-control @error('feedback') is-invalid @enderror" rows="3">{{ old('feedback', $visit->feedback ?? '') }}</textarea>
        <div class="form-text">Use this for backfilling a visit recorded on paper — no GPS/photo capture happens here.</div>
        @error('feedback') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($visit) ? 'Update Visit' : 'Record Visit' }}</button>
    <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>
