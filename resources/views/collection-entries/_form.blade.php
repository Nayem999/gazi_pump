@csrf
@if (isset($collectionEntry))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" @selected((string) old('user_id', $collectionEntry->user_id ?? '') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Dealer <span class="text-danger">*</span></label>
        <select id="dealerSelect" name="dealer_id" class="form-select @error('dealer_id') is-invalid @enderror" required>
            <option value="">— Select Dealer —</option>
            @foreach ($dealers as $dealer)
                <option value="{{ $dealer->id }}" data-balance="{{ $outstandingBalances[$dealer->id] ?? 0 }}" @selected((string) old('dealer_id', $collectionEntry->dealer_id ?? '') === (string) $dealer->id)>{{ $dealer->name }} ({{ $dealer->dealer_code }})</option>
            @endforeach
        </select>
        @error('dealer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="alert alert-info d-flex justify-content-between mb-0">
            <span>Outstanding Balance</span>
            <strong id="balancePreview">—</strong>
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Collection Date <span class="text-danger">*</span></label>
        <input type="date" name="collection_date" class="form-control @error('collection_date') is-invalid @enderror"
               value="{{ old('collection_date', isset($collectionEntry) ? $collectionEntry->collection_date->toDateString() : now()->toDateString()) }}" required>
        @error('collection_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" min="0.01" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror"
               value="{{ old('amount', $collectionEntry->amount ?? '') }}" required>
        <div class="form-text">Up to {{ config('sfa.collections.overpayment_tolerance_percent') }}% over balance is allowed.</div>
        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
            @foreach ($paymentMethods as $method)
                <option value="{{ $method->value }}" @selected((string) old('payment_method', $collectionEntry->payment_method->value ?? '') === $method->value)>{{ $method->label() }}</option>
            @endforeach
        </select>
        @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Reference No.</label>
        <input type="text" name="reference_no" class="form-control @error('reference_no') is-invalid @enderror"
               value="{{ old('reference_no', $collectionEntry->reference_no ?? '') }}" placeholder="Cheque no. / transaction ID">
        @error('reference_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $collectionEntry->remarks ?? '') }}</textarea>
        @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>{{ isset($collectionEntry) ? 'Update Collection' : 'Record Collection' }}</button>
    <a href="{{ route('collection-entries.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        (function () {
            const dealerSelect = document.getElementById('dealerSelect');
            const balancePreview = document.getElementById('balancePreview');

            function updateBalance() {
                const option = dealerSelect.options[dealerSelect.selectedIndex];
                const balance = option?.dataset.balance;
                balancePreview.textContent = balance ? parseFloat(balance).toFixed(2) : '—';
            }

            dealerSelect?.addEventListener('change', updateBalance);
            updateBalance();
        })();
    </script>
@endpush
