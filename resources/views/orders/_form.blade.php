@php
    if (old('items')) {
        $existingItemsForJs = collect(old('items'))->values()->all();
    } elseif (isset($order)) {
        $existingItemsForJs = $order->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'discount_amount' => (float) $item->discount_amount,
        ])->all();
    } else {
        $existingItemsForJs = [];
    }
@endphp

@php
    // A plain Sales Executive can only ever record an order for themself —
    // locked to their own name instead of offered as a choice. The select
    // stays disabled (so it can't be tampered with via the UI) and a
    // matching submit-time re-enable below keeps its value in the POST
    // body, same pattern already used for the Amount field's OTP lock.
    $lockExecutiveField = auth()->user()->isSalesExecutiveOnly();
@endphp

@csrf
@if (isset($order))
    @method('PUT')
@endif

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Sales Executive <span class="text-danger">*</span></label>
        <select name="user_id" id="executiveSelect" class="form-select @error('user_id') is-invalid @enderror" required @disabled($lockExecutiveField)>
            <option value="">— Select Executive —</option>
            @foreach ($executives as $executive)
                <option value="{{ $executive->id }}" @selected((string) old('user_id', $order->user_id ?? ($lockExecutiveField ? auth()->id() : '')) === (string) $executive->id)>{{ $executive->name }} ({{ $executive->employee_id }})</option>
            @endforeach
        </select>
        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Dealer <span class="text-danger">*</span></label>
        <select name="dealer_id" class="form-select @error('dealer_id') is-invalid @enderror" required>
            <option value="">— Select Dealer —</option>
            @foreach ($dealers as $dealer)
                <option value="{{ $dealer->id }}" @selected((string) old('dealer_id', $order->dealer_id ?? '') === (string) $dealer->id)>{{ $dealer->name }} ({{ $dealer->dealer_code }})</option>
            @endforeach
        </select>
        @error('dealer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Retailer</label>
        <select name="retailer_id" id="retailerSelect" class="form-select @error('retailer_id') is-invalid @enderror">
            <option value="">— None —</option>
            @foreach ($retailers as $retailer)
                <option value="{{ $retailer->id }}" data-dealer-id="{{ $retailer->dealer_id }}" @selected((string) old('retailer_id', $order->retailer_id ?? '') === (string) $retailer->id)>{{ $retailer->name }}</option>
            @endforeach
        </select>
        @error('retailer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Order Date <span class="text-danger">*</span></label>
        <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror"
               value="{{ old('order_date', isset($order) ? $order->order_date->toDateString() : now()->toDateString()) }}" required>
        @error('order_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label mb-2">Products <span class="text-danger">*</span></label>
        @error('items') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

        <div class="table-responsive">
            <table class="table align-middle" id="itemsTable">
                <thead>
                    <tr>
                        <th style="min-width:220px">Product</th>
                        <th style="width:100px">Quantity</th>
                        <th style="width:130px">Unit Price</th>
                        <th style="width:130px">Discount</th>
                        <th style="width:130px">Line Total</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>

        <button type="button" id="addItemBtn" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-plus me-1"></i>Add Product
        </button>

        <div class="alert alert-info d-flex justify-content-between mt-3 mb-0">
            <span>Grand Total</span>
            <strong id="grandTotalPreview">0.00</strong>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks', $order->remarks ?? '') }}</textarea>
        @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="button" id="previewOrderBtn" class="btn btn-primary"><i class="ti ti-eye me-1"></i>Preview Order</button>
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

{{--
    Mandatory Preview step before final submission (spec §11) — built from
    the form's own current state via JS, no extra server round-trip. EDIT
    just closes the modal so the rep keeps adjusting the form; SUBMIT ORDER
    triggers the real submission; CANCEL abandons the order and returns to
    the list, same as the plain Cancel link above.
--}}
<x-modal id="orderPreviewModal" title="Preview Order" size="lg">
    <dl class="row mb-3">
        <dt class="col-sm-4">Sales Executive</dt>
        <dd class="col-sm-8" id="previewExecutive"></dd>
        <dt class="col-sm-4">Dealer</dt>
        <dd class="col-sm-8" id="previewDealer"></dd>
        <dt class="col-sm-4">Retailer</dt>
        <dd class="col-sm-8" id="previewRetailer"></dd>
        <dt class="col-sm-4">Order Date</dt>
        <dd class="col-sm-8" id="previewOrderDate"></dd>
    </dl>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Discount</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody id="previewItemsBody"></tbody>
        </table>
    </div>

    <div class="alert alert-info d-flex justify-content-between mb-3">
        <span>Grand Total</span>
        <strong id="previewGrandTotal">0.00</strong>
    </div>

    <div>
        <dt class="small text-muted">Remarks</dt>
        <dd id="previewRemarks" class="mb-0"></dd>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i class="ti ti-pencil me-1"></i>Edit</button>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-danger">Cancel</a>
        <button type="button" id="confirmSubmitOrderBtn" class="btn btn-primary"><i class="ti ti-check me-1"></i>Submit Order</button>
    </x-slot:footer>
</x-modal>

{{-- Product options shared by every line-item row --}}
<template id="productOptionsTemplate">
    <option value="">— Select Product —</option>
    @foreach ($products as $product)
        <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} ({{ $product->sku }})</option>
    @endforeach
</template>

@push('scripts')
    <script>
        // Wrapped in DOMContentLoaded: this inline script runs synchronously
        // as the parser reaches it, but window.$ (jQuery, needed by the
        // product select's Select2 change binding below) is only defined
        // once the deferred module bundle (app.js) finishes executing —
        // which happens at/before DOMContentLoaded, not before.
        document.addEventListener('DOMContentLoaded', function () {
            // A disabled select's value is never included in form submission
            // — re-enable it right before the browser serializes the form so
            // the locked-in executive still gets sent (same pattern as the
            // Amount field's OTP lock in collection-entries/_form).
            const executiveSelect = document.getElementById('executiveSelect');
            executiveSelect?.closest('form')?.addEventListener('submit', function () {
                executiveSelect.disabled = false;
            });

            // Retailer options are all pre-rendered (small per-dealer counts
            // don't justify an AJAX round trip like the Territory/Thana
            // cascades use) — switching Dealer just hides/disables every
            // option whose data-dealer-id doesn't match, rather than
            // re-fetching a filtered list from the server.
            const dealerSelect = document.querySelector('select[name="dealer_id"]');
            const retailerSelect = document.getElementById('retailerSelect');

            function filterRetailers(preserveSelection) {
                const dealerId = dealerSelect.value;
                const currentValue = retailerSelect.value;
                let currentStillValid = false;

                Array.from(retailerSelect.options).forEach((option) => {
                    if (!option.value) {
                        return;
                    }

                    const matches = option.dataset.dealerId === dealerId;
                    option.hidden = !matches;
                    option.disabled = !matches;

                    if (matches && option.value === currentValue) {
                        currentStillValid = true;
                    }
                });

                if (!preserveSelection || !currentStillValid) {
                    retailerSelect.value = '';
                }

                window.refreshSelect2?.(retailerSelect);
            }

            window.$(dealerSelect).on('change', function () {
                filterRetailers(false);
            });

            filterRetailers(true);

            const itemsBody = document.getElementById('itemsBody');
            const addItemBtn = document.getElementById('addItemBtn');
            const grandTotalPreview = document.getElementById('grandTotalPreview');
            const productOptionsHtml = document.getElementById('productOptionsTemplate').innerHTML;
            const maxDiscountPercent = {{ (float) config('sfa.orders.max_discount_percent') }};
            let rowIndex = 0;

            function buildRow(item) {
                item = item || {};
                const index = rowIndex++;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="items[${index}][product_id]" class="form-select form-select-sm product-select" required>${productOptionsHtml}</select>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" class="form-control form-control-sm quantity-input" min="1" step="1" value="${item.quantity || 1}" required>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][unit_price]" class="form-control form-control-sm unit-price-input" min="0" step="0.01" value="${item.unit_price || ''}" required>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][discount_amount]" class="form-control form-control-sm discount-input" min="0" step="0.01" value="${item.discount_amount || 0}">
                    </td>
                    <td class="line-total-preview fw-semibold">0.00</td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="ti ti-trash"></i></button>
                    </td>
                `;
                itemsBody.appendChild(tr);

                const productSelect = tr.querySelector('.product-select');
                const quantityInput = tr.querySelector('.quantity-input');
                const unitPriceInput = tr.querySelector('.unit-price-input');
                const discountInput = tr.querySelector('.discount-input');

                if (item.product_id) {
                    productSelect.value = item.product_id;
                }

                // Bound through jQuery, not addEventListener: window.initSelect2()
                // below attaches Select2 to this row's product select, which
                // (like every other Select2 field in this app) changes its
                // value via jQuery's own event system rather than dispatching
                // a plain native "change" addEventListener would catch.
                window.$(productSelect).on('change', function () {
                    const option = productSelect.options[productSelect.selectedIndex];
                    const price = option?.dataset.price;
                    if (price && !unitPriceInput.value) {
                        unitPriceInput.value = parseFloat(price).toFixed(2);
                    }
                    recalculate();
                });

                [quantityInput, unitPriceInput, discountInput].forEach((el) => el.addEventListener('input', recalculate));
                tr.querySelector('.remove-item-btn').addEventListener('click', function () {
                    tr.remove();
                    recalculate();
                });

                // Newly inserted after the page's initial Select2 scan, so it
                // needs its own pass — see select2-init.js.
                window.initSelect2?.();

                return tr;
            }

            function recalculate() {
                let grandTotal = 0;

                itemsBody.querySelectorAll('tr').forEach((tr) => {
                    const quantity = parseFloat(tr.querySelector('.quantity-input').value) || 0;
                    const unitPrice = parseFloat(tr.querySelector('.unit-price-input').value) || 0;
                    const discount = parseFloat(tr.querySelector('.discount-input').value) || 0;
                    const subtotal = quantity * unitPrice;
                    const lineTotal = Math.max(0, subtotal - discount);
                    tr.querySelector('.line-total-preview').textContent = lineTotal.toFixed(2);

                    const discountInput = tr.querySelector('.discount-input');
                    const maxDiscount = subtotal * (maxDiscountPercent / 100);
                    discountInput.classList.toggle('is-invalid', discount > maxDiscount);

                    grandTotal += lineTotal;
                });

                grandTotalPreview.textContent = grandTotal.toFixed(2);
            }

            addItemBtn.addEventListener('click', function () {
                buildRow();
                recalculate();
            });

            const existingItems = @json($existingItemsForJs);

            if (existingItems.length > 0) {
                existingItems.forEach((item) => buildRow(item));
            } else {
                buildRow();
            }

            recalculate();

            // --- Mandatory Preview step (spec §11) ---
            const form = document.getElementById('previewOrderBtn').closest('form');
            const previewModalEl = document.getElementById('orderPreviewModal');
            const previewModal = new window.bootstrap.Modal(previewModalEl);

            document.getElementById('previewOrderBtn').addEventListener('click', function () {
                if (!form.reportValidity()) {
                    return;
                }

                const executiveOption = executiveSelect?.selectedOptions[0];
                document.getElementById('previewExecutive').textContent = executiveOption ? executiveOption.text : '—';

                const dealerOption = dealerSelect.selectedOptions[0];
                document.getElementById('previewDealer').textContent = dealerOption && dealerOption.value ? dealerOption.text : '—';

                const retailerOption = retailerSelect.selectedOptions[0];
                document.getElementById('previewRetailer').textContent = retailerOption && retailerOption.value ? retailerOption.text : '— None —';

                document.getElementById('previewOrderDate').textContent = document.querySelector('input[name="order_date"]').value || '—';
                document.getElementById('previewRemarks').textContent = document.querySelector('textarea[name="remarks"]').value || '—';

                const previewBody = document.getElementById('previewItemsBody');
                previewBody.innerHTML = '';
                itemsBody.querySelectorAll('tr').forEach((tr) => {
                    const productOption = tr.querySelector('.product-select').selectedOptions[0];
                    const quantity = tr.querySelector('.quantity-input').value;
                    const unitPrice = parseFloat(tr.querySelector('.unit-price-input').value || 0);
                    const discount = parseFloat(tr.querySelector('.discount-input').value || 0);
                    const lineTotal = tr.querySelector('.line-total-preview').textContent;

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${productOption ? productOption.text : '—'}</td>
                        <td>${quantity}</td>
                        <td>${unitPrice.toFixed(2)}</td>
                        <td>${discount.toFixed(2)}</td>
                        <td>${lineTotal}</td>
                    `;
                    previewBody.appendChild(row);
                });

                document.getElementById('previewGrandTotal').textContent = grandTotalPreview.textContent;

                previewModal.show();
            });

            document.getElementById('confirmSubmitOrderBtn').addEventListener('click', function () {
                previewModal.hide();
                form.requestSubmit();
            });
        });
    </script>
@endpush
