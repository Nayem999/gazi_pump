@extends('layouts.admin')

@section('title', 'Record Cash Handover')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cash-handovers.index') }}">Cash Handover</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('cash-handovers.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
                        <select id="executiveSelect" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">— Select Executive —</option>
                            @foreach ($executives as $executive)
                                <option value="{{ $executive->id }}" data-cash-in-hand="{{ $cashInHand[$executive->id] ?? 0 }}" @selected((string) old('user_id') === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <div class="alert d-flex justify-content-between mb-0" id="cashInHandAlert">
                            <span>Cash in Hand</span>
                            <strong id="cashInHandPreview">—</strong>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" min="0.01" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount') }}" required>
                        <div class="form-text">Cannot exceed the executive's current cash in hand.</div>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Handover Date <span class="text-danger">*</span></label>
                        <input type="date" name="handover_date" class="form-control @error('handover_date') is-invalid @enderror"
                               value="{{ old('handover_date', now()->toDateString()) }}" required>
                        @error('handover_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks') }}</textarea>
                        @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Record Handover</button>
                    <a href="{{ route('cash-handovers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const executiveSelect = document.getElementById('executiveSelect');
            const cashInHandPreview = document.getElementById('cashInHandPreview');
            const cashInHandAlert = document.getElementById('cashInHandAlert');
            const dailyLimit = @json($dailyLimit);

            function updateCashInHand() {
                const option = executiveSelect.options[executiveSelect.selectedIndex];
                const cashInHand = option?.dataset.cashInHand;

                if (! cashInHand) {
                    cashInHandPreview.textContent = '—';
                    cashInHandAlert.className = 'alert alert-secondary d-flex justify-content-between mb-0';
                    return;
                }

                const amount = parseFloat(cashInHand);
                const overLimit = dailyLimit && amount > dailyLimit;
                cashInHandPreview.textContent = '৳ ' + amount.toFixed(2) + (dailyLimit ? ' / limit ৳ ' + parseFloat(dailyLimit).toFixed(2) : '');
                cashInHandAlert.className = 'alert ' + (overLimit ? 'alert-danger' : 'alert-info') + ' d-flex justify-content-between mb-0';
            }

            // Bound through jQuery: this select is enhanced by Select2 (see
            // select2-init.js), which changes its value via jQuery's own
            // event system rather than a native "change" event.
            if (executiveSelect) {
                window.$(executiveSelect).on('change', updateCashInHand);
                updateCashInHand();
            }
        });
    </script>
@endpush
