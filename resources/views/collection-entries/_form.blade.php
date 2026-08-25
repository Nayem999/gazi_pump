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
        <select name="payment_method" id="paymentMethodSelect" class="form-select @error('payment_method') is-invalid @enderror" required>
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

    @php
        $isCheque = (string) old('payment_method', $collectionEntry->payment_method->value ?? '') === \App\Enums\PaymentMethod::Cheque->value;
        // Already has an image on file (edit page) → not force-required on
        // this submission even while the field stays visible for cheque.
        $hasExistingChequeImage = isset($collectionEntry) && (bool) $collectionEntry->cheque_image;
    @endphp
    <div class="col-md-6" id="chequeImageField" style="{{ $isCheque ? '' : 'display:none' }}">
        <label class="form-label">
            Cheque Image
            <span class="text-danger" id="chequeImageRequiredMark" style="{{ $hasExistingChequeImage ? 'display:none' : '' }}">*</span>
        </label>
        <input type="file" name="cheque_image" id="chequeImageInput" accept="image/*" class="form-control @error('cheque_image') is-invalid @enderror"
               data-has-existing="{{ $hasExistingChequeImage ? '1' : '0' }}" @required($isCheque && ! $hasExistingChequeImage)>
        <div class="form-text">Photo of the physical cheque, for reference.</div>
        @error('cheque_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if (isset($collectionEntry) && $collectionEntry->chequeImageUrl())
            <img id="chequeImagePreview" src="{{ $collectionEntry->chequeImageUrl() }}" class="mt-2 rounded" style="height:80px">
        @else
            <img id="chequeImagePreview" class="mt-2 rounded d-none" style="height:80px">
        @endif
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
        // Wrapped in DOMContentLoaded: this inline script runs synchronously
        // as the parser reaches it, but window.$ (jQuery) is only defined
        // once the deferred module bundle (app.js) finishes executing —
        // which happens at/before DOMContentLoaded, not before. Calling
        // window.$(...) outside this wrapper throws "window.$ is not a
        // function" and silently skips the rest of the block, which is why
        // the balance preview never updated.
        document.addEventListener('DOMContentLoaded', function () {
            const dealerSelect = document.getElementById('dealerSelect');
            const balancePreview = document.getElementById('balancePreview');

            function updateBalance() {
                const option = dealerSelect.options[dealerSelect.selectedIndex];
                const balance = option?.dataset.balance;
                balancePreview.textContent = balance ? parseFloat(balance).toFixed(2) : '—';
            }

            // Bound through jQuery, not addEventListener: Select2 (see
            // select2-init.js) attaches to this select and changes its value
            // via jQuery's own event system, which a plain addEventListener
            // wouldn't catch.
            if (dealerSelect) {
                window.$(dealerSelect).on('change', updateBalance);
                updateBalance();
            }

            // payment_method isn't matched by select2-init.js's selector
            // list, so it stays a plain native <select> — a regular
            // addEventListener is fine here.
            const paymentMethodSelect = document.getElementById('paymentMethodSelect');
            const chequeImageField = document.getElementById('chequeImageField');
            const chequeImageInput = document.getElementById('chequeImageInput');
            const chequeImagePreview = document.getElementById('chequeImagePreview');

            const chequeImageRequiredMark = document.getElementById('chequeImageRequiredMark');
            const chequeImageHasExisting = chequeImageInput?.dataset.hasExisting === '1';

            if (paymentMethodSelect && chequeImageField) {
                paymentMethodSelect.addEventListener('change', function () {
                    const isCheque = paymentMethodSelect.value === 'cheque';
                    chequeImageField.style.display = isCheque ? '' : 'none';

                    // Only force the field when it's cheque AND there isn't
                    // already an image on file (editing an existing cheque
                    // collection without touching this field keeps its
                    // current image rather than demanding a re-upload).
                    const isRequired = isCheque && ! chequeImageHasExisting;
                    chequeImageInput.required = isRequired;
                    chequeImageRequiredMark.style.display = isRequired ? '' : 'none';
                });
            }

            chequeImageInput?.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (! file) {
                    return;
                }
                chequeImagePreview.src = URL.createObjectURL(file);
                chequeImagePreview.classList.remove('d-none');
            });
        });
    </script>
@endpush
